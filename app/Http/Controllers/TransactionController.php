<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Service;
use App\Models\Customer;
use App\Services\MidtransService;
use App\Services\FonnteService; // Pastikan ini ada kalau belum
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Tambah ini untuk Log
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    protected $midtransService;
    protected $fonnteService;

    public function __construct(MidtransService $midtransService, FonnteService $fonnteService)
    {
        $this->midtransService = $midtransService;
        $this->fonnteService = $fonnteService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'service'])
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status pembayaran
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter berdasarkan tanggal
        if ($request->has('start_date') && $request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        $transactions = $query->paginate(10);

        // Stats
        $successCount = Transaction::where('payment_status', 'paid')->count();
        $pendingCount = Transaction::where('payment_status', 'pending')->count();
        $failedCount = Transaction::whereIn('payment_status', ['failed', 'expired'])->count();

        return view('transactions.index', compact(
            'transactions',
            'successCount',
            'pendingCount',
            'failedCount'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $services = Service::active()->get();
        $customers = Customer::orderBy('name')->get();

        return view('transactions.create', compact('services', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'     => 'required|string|max:100',
            'customer_phone'    => 'nullable|string|max:20',
            'customer_address'  => 'nullable|string|max:500',
            'service_id'        => 'required|exists:services,id',
            'quantity'          => 'required|numeric|min:0.1',
            'payment_method'    => 'required|in:cash,midtrans,transfer',
            'notes'             => 'nullable|string|max:500'
        ]);

        Log::info('Transaction Store Started', $validated);

        try {
            DB::beginTransaction();

            // 1. Cari atau buat customer
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['customer_phone'] ?: 'N/A-' . time()],
                [
                    'name'    => $validated['customer_name'],
                    'address' => $validated['customer_address'] ?? '-'
                ]
            );

            // 2. Hitung total
            $service = Service::findOrFail($validated['service_id']);
            $totalAmount = $service->price * $validated['quantity'];

            // 3. Generate order ID yang unik
            $orderId = 'ORD-' . Str::random(10) . '-' . time();

            // 4. Buat transaksi (invoice_number akan auto-generate dari model)
            $transaction = Transaction::create([
                'transaction_date'  => now(),
                'customer_id'       => $customer->id,
                'service_id'        => $validated['service_id'],
                'quantity'          => $validated['quantity'],
                'price'             => $service->price,
                'total_amount'      => $totalAmount,
                'payment_method'    => $validated['payment_method'],
                'payment_status'    => $validated['payment_method'] === 'cash' ? 'paid' : 'pending',
                'notes'             => $validated['notes'] ?? null,
                'midtrans_order_id' => $validated['payment_method'] === 'cash' ? null : $orderId,
            ]);

            Log::info('Transaction created', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'order_id' => $orderId,
                'payment_method' => $validated['payment_method']
            ]);

            // 5. Jika Midtrans, buat snap token
            if ($validated['payment_method'] === 'midtrans') {
                Log::info('Processing Midtrans payment');

                $response = $this->midtransService->createTransaction($transaction);

                Log::info('Midtrans response', $response);

                if (!$response['success']) {
                    throw new \Exception('Gagal generate token Midtrans: ' . $response['message']);
                }

                $transaction->update([
                    'midtrans_snap_token'  => $response['snap_token'],
                    'midtrans_payment_url' => $response['payment_url'],
                ]);

                Log::info('Transaction updated with snap token', [
                    'snap_token' => substr($response['snap_token'], 0, 50) . '...'
                ]);

                DB::commit();

                Log::info('Redirecting to snap payment page');

                // Redirect ke halaman snap payment
                return redirect()->route('transactions.snap', $transaction);
            }

            DB::commit();

            // Kirim resi otomatis untuk cash & transfer
            $this->generateAndSendResi($transaction);

            Log::info('Transaction completed & WhatsApp receipt sent', [
                'invoice' => $transaction->invoice_number
            ]);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi berhasil! Resi telah dikirim ke WhatsApp pelanggan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Gagal membuat transaksi: ' . $e->getMessage()])->withInput();
        }
    }

    public function snapPayment(Transaction $transaction)
    {
        // Debug info
        Log::info('Snap Payment Accessed', [
            'transaction_id' => $transaction->id,
            'order_id' => $transaction->midtrans_order_id,
            'snap_token' => $transaction->midtrans_snap_token ? 'exists' : 'missing',
            'payment_method' => $transaction->payment_method,
            'payment_status' => $transaction->payment_status
        ]);

        if ($transaction->payment_method !== 'midtrans') {
            return redirect()->route('transactions.index')
                ->with('error', 'Transaksi ini bukan pembayaran Midtrans.');
        }

        if (!$transaction->midtrans_snap_token) {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'Token pembayaran tidak tersedia. Silakan hubungi admin.');
        }

        return view('transactions.snap', compact('transaction'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['customer', 'service']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $services = Service::active()->get();
        $customers = Customer::all();

        return view('transactions.edit', compact('transaction', 'services', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|numeric|min:0.1',
            'payment_method' => 'required|in:cash,midtrans,transfer',
            'payment_status' => 'required|in:pending,paid,failed,expired',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $service = Service::findOrFail($validated['service_id']);
            $totalAmount = $service->price * $validated['quantity'];

            $updateData = [
                'customer_id' => $validated['customer_id'],
                'service_id' => $validated['service_id'],
                'quantity' => $validated['quantity'],
                'price' => $service->price,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes']
            ];

            $transaction->update($updateData);

            DB::commit();

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        try {
            $transaction->delete();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Handle Midtrans payment notification
     */
    public function handleMidtransNotification(Request $request)
    {
        $result = $this->midtransService->handleNotification();

        // Jika sukses update status dari MidtransService
        if ($result['success']) {
            $transaction = $result['transaction'] ?? null; // Pastikan MidtransService return transaction

            if ($transaction &&
                $transaction->wasChanged('payment_status') &&
                $transaction->payment_status === 'paid') {

                $this->generateAndSendResi($transaction);
            }

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failed', 'message' => $result['message'] ?? 'Unknown error'], 400);
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Transaction $transaction)
    {
        if (!$transaction->midtrans_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'This transaction does not have Midtrans payment'
            ], 400);
        }

        $result = $this->midtransService->checkStatus($transaction->midtrans_order_id);

        if ($result['success']) {
            // Update transaction status based on Midtrans response
            $status = $result['status']->transaction_status;

            $paymentStatus = match ($status) {
                'capture', 'settlement' => 'paid',
                'pending' => 'pending',
                'deny', 'cancel' => 'failed',
                'expire' => 'expired',
                default => 'pending'
            };

            $transaction->update([
                'payment_status' => $paymentStatus,
                'midtrans_transaction_status' => $status,
                'midtrans_payment_type' => $result['status']->payment_type ?? null,
                'midtrans_transaction_id' => $result['status']->transaction_id ?? null,
            ]);

            return response()->json([
                'success' => true,
                'payment_status' => $paymentStatus,
                'transaction_status' => $status
            ]);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Kirim resi dalam bentuk pesan teks WhatsApp (via Fonnte)
     */
    protected function generateAndSendResi(Transaction $transaction)
    {
        // 1. Cek apakah customer punya nomor HP
        if (!$transaction->customer || !$transaction->customer->phone) {
            Log::info('Skip kirim resi WA: nomor HP kosong', [
                'transaction_id' => $transaction->id ?? 'unknown'
            ]);
            return;
        }

        // 2. Cek apakah status sudah PAID
        if ($transaction->payment_status !== 'paid') {
            Log::info('Skip kirim resi WA: status bukan paid', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->payment_status
            ]);
            return;
        }

        // 3. Kirim pesan teks cantik via Fonnte
        $sent = $this->fonnteService->sendResiText($transaction->customer->phone, $transaction);

        if ($sent) {
            Log::info('Resi WhatsApp berhasil dikirim', [
                'invoice'     => $transaction->invoice_number,
                'customer'    => $transaction->customer->name,
                'phone'       => $transaction->customer->phone,
                'total'       => $transaction->total_amount
            ]);
        } else {
            Log::warning('Gagal kirim resi WhatsApp', [
                'invoice' => $transaction->invoice_number,
                'phone'   => $transaction->customer->phone
            ]);
        }
    }

    /**
     * Kirim ulang resi WhatsApp dari halaman detail
     */
    public function resendWhatsapp(Transaction $transaction)
    {
        // Cek apakah sudah pernah dibayar
        if ($transaction->payment_status !== 'paid') {
            return back()->with('error', 'Resi hanya bisa dikirim ulang untuk transaksi yang sudah dibayar (PAID).');
        }

        // Kirim ulang
        $this->generateAndSendResi($transaction);

        return back()->with('success', 'Resi berhasil dikirim ulang ke WhatsApp pelanggan!');
    }
}
