<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Service;
use App\Models\Customer;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    protected $midtransService;
    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
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

    /**
     * Store a newly created resource in storage.
     */
   /**
 * Store a newly created resource in storage.
 */
/**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    // Validasi input
    $validated = $request->validate([
        'customer_name' => 'required|string|max:100',
        'customer_phone' => 'nullable|string|max:20',
        'customer_address' => 'nullable|string|max:500',
        'service_id' => 'required|exists:services,id', // Pastikan services table ada
        'quantity' => 'required|numeric|min:0.1',
        'payment_method' => 'required|in:cash,midtrans,transfer',
        'notes' => 'nullable|string|max:500'
    ], [
        'service_id.exists' => 'Layanan yang dipilih tidak valid. Silakan pilih layanan yang tersedia.',
        'customer_name.required' => 'Nama pelanggan harus diisi.',
        'quantity.required' => 'Jumlah harus diisi.',
        'quantity.min' => 'Jumlah minimal 0.1.',
    ]);

    try {
        DB::beginTransaction();

        // Find or create customer
        $customer = Customer::firstOrCreate(
            [
                'phone' => $validated['customer_phone'] ?: 'unknown-'.time()
            ],
            [
                'name' => $validated['customer_name'],
                'address' => $validated['customer_address'] ?? '-'
            ]
        );

        // Update customer data if different
        if ($customer->name !== $validated['customer_name'] || $customer->address !== $validated['customer_address']) {
            $customer->update([
                'name' => $validated['customer_name'],
                'address' => $validated['customer_address'] ?? $customer->address
            ]);
        }

        // Get service with validation
        $service = Service::find($validated['service_id']);

        if (!$service) {
            throw new \Exception('Layanan tidak ditemukan.');
        }

        $totalAmount = $service->price * $validated['quantity'];

        $transactionData = [
            'transaction_date' => now(),
            'customer_id' => $customer->id,
            'service_id' => $validated['service_id'],
            'quantity' => $validated['quantity'],
            'price' => $service->price,
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_method'] == 'cash' ? 'paid' : 'pending',
            'notes' => $validated['notes']
        ];

        // Generate Midtrans order ID untuk pembayaran digital
        if ($validated['payment_method'] !== 'cash') {
            $transactionData['midtrans_order_id'] = 'ORDER-' . Str::uuid();
        }

        $transaction = Transaction::create($transactionData);

        // Handle Midtrans payment
        if ($validated['payment_method'] === 'midtrans') {
            $midtransResponse = $this->midtransService->createTransaction($transaction);

            if (!$midtransResponse['success']) {
                throw new \Exception($midtransResponse['message']);
            }

            $transaction->update([
                'midtrans_payment_url' => $midtransResponse['payment_url']
            ]);
        }

        DB::commit();

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil dibuat! 📝');

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->back()
            ->with('error', 'Gagal membuat transaksi: ' . $e->getMessage())
            ->withInput();
    }
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
        // Log notifikasi untuk debugging
        \Log::info('Midtrans Notification Received', $request->all());

        try {
            // Validasi notifikasi key (jika ada)
            // $notificationKey = $request->header('X-Notification-Key');
            // if ($notificationKey !== env('MIDTRANS_NOTIFICATION_KEY')) {
            //     return response()->json(['error' => 'Invalid notification key'], 401);
            // }

            $orderId = $request->input('order_id');
            $transactionStatus = $request->input('transaction_status');
            $paymentType = $request->input('payment_type');
            $transactionId = $request->input('transaction_id');

            // Cari transaksi berdasarkan order_id
            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

            if (!$transaction) {
                \Log::warning('Transaction not found for order_id: ' . $orderId);
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Map status dari Midtrans ke payment_status aplikasi
            $paymentStatus = match($transactionStatus) {
                'capture', 'settlement' => 'paid',
                'pending' => 'pending',
                'deny', 'cancel', 'failure' => 'failed',
                'expire' => 'expired',
                default => 'pending'
            };

            // Update transaction
            $transaction->update([
                'midtrans_transaction_status' => $transactionStatus,
                'midtrans_transaction_id' => $transactionId,
                'midtrans_payment_type' => $paymentType,
                'payment_status' => $paymentStatus,
                'transaction_date' => now() // Update waktu transaksi jika belum
            ]);

            \Log::info('Transaction updated', [
                'order_id' => $orderId,
                'payment_status' => $paymentStatus,
                'transaction_status' => $transactionStatus
            ]);

            // Kirim WhatsApp jika pembayaran sudah paid
            if ($paymentStatus === 'paid' && $transaction->customer->phone) {
                try {
                    \App\Services\FonnteService::sendReceipt($transaction);
                } catch (\Exception $e) {
                    \Log::error('Failed to send WhatsApp receipt', ['error' => $e->getMessage()]);
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            \Log::error('Error handling Midtrans notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
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
                $transactionStatus = $midtransStatus->transaction_status ?? 'pending';

                // Map status
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
                        'midtrans_transaction_id' => $midtransStatus->transaction_id ?? $transaction->midtrans_transaction_id,
                        'midtrans_payment_type' => $midtransStatus->payment_type ?? $transaction->midtrans_payment_type
                    ]);

                    \Log::info('Payment status updated via check', [
                        'order_id' => $transaction->midtrans_order_id,
                        'old_status' => $transaction->getOriginal('payment_status'),
                        'new_status' => $paymentStatus
                    ]);

                    // Kirim WhatsApp jika baru dibayar
                    if ($paymentStatus === 'paid' && $transaction->customer->phone) {
                        try {
                            \App\Services\FonnteService::sendReceipt($transaction);
                        } catch (\Exception $e) {
                            \Log::error('Failed to send WhatsApp receipt', ['error' => $e->getMessage()]);
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
            \Log::error('Error checking payment status', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
