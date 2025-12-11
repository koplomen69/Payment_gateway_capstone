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
use Illuminate\Support\Facades\Schema;
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

                // Prepare update data; midtrans_snap_token might not exist in DB
                $updateData = [
                    'midtrans_payment_url' => $response['payment_url'],
                    'midtrans_transaction_status' => $transaction->midtrans_transaction_status ?? null,
                ];

                if (Schema::hasColumn('transactions', 'midtrans_snap_token')) {
                    $updateData['midtrans_snap_token'] = $response['snap_token'] ?? null;
                }

                $transaction->update($updateData);

                Log::info('Transaction updated with midtrans response', [
                    'has_snap_column' => Schema::hasColumn('transactions', 'midtrans_snap_token') ? true : false,
                    'payment_url' => $response['payment_url'] ?? null,
                ]);

                DB::commit();

                Log::info('Redirecting to payment gateway');

                // If snap token saved, redirect to internal snap page (uses snap.js). Otherwise redirect directly to Midtrans payment URL.
                if (!empty($updateData['midtrans_snap_token'])) {
                    return redirect()->route('transactions.snap', $transaction);
                }

                return redirect($response['payment_url']);
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

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Transaction $transaction)
    {
        if (!$transaction->midtrans_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini tidak memiliki order ID Midtrans'
            ], 400);
        }

        try {
            // Cek status ke Midtrans API
            $result = $this->midtransService->checkStatus($transaction->midtrans_order_id);

            if ($result['success']) {
                $midtransStatus = $result['status'];
                $transactionStatus = is_array($midtransStatus) ? ($midtransStatus['transaction_status'] ?? 'pending') : ($midtransStatus->transaction_status ?? 'pending');

            $paymentStatus = match($transactionStatus) {
                'capture', 'settlement' => 'paid',
                'pending' => 'pending',
                'deny', 'cancel', 'failure' => 'failed',
                'expire' => 'expired',
                default => 'pending'
            };

                // Update transaksi jika status berbeda
                if ($transaction->payment_status !== $paymentStatus) {
                    $transaction->update([
                        'payment_status' => $paymentStatus,
                        'midtrans_transaction_status' => $transactionStatus,
                        'midtrans_transaction_id' => $midtransStatus['transaction_id'] ?? $transaction->midtrans_transaction_id,
                        'midtrans_payment_type' => $midtransStatus['payment_type'] ?? $transaction->midtrans_payment_type
                    ]);

                    Log::info('Payment status updated via check', [
                        'order_id' => $transaction->midtrans_order_id,
                        'old_status' => $transaction->getOriginal('payment_status'),
                        'new_status' => $paymentStatus
                    ]);

                    // Kirim WhatsApp jika baru dibayar
                    if ($paymentStatus === 'paid' && $transaction->customer->phone) {
                        try {
                            $this->fonnteService->sendResiText($transaction->customer->phone ?? '', $transaction);
                        } catch (\Exception $e) {
                            Log::error('Failed to send WhatsApp receipt', ['error' => $e->getMessage()]);
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'payment_status' => $paymentStatus,
                    'transaction_status' => $transactionStatus,
                    'message' => 'Status berhasil diperbarui'
                ]);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            Log::error('Error checking payment status', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status pembayaran: ' . $e->getMessage()
            ], 500);
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
