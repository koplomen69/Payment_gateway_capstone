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

    // INI YANG SEBELUMNYA HILANG → SEKARANG SUDAH ADA!
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'service'])->latest();

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $transactions = $query->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $services = Service::active()->get();
        $customers = Customer::all();
        return view('transactions.create', compact('services', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|numeric|min:0.1',
            'payment_method' => 'required|in:cash,midtrans,transfer',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $service = Service::findOrFail($validated['service_id']);
            $totalAmount = $service->price * $validated['quantity'];

            $transactionData = [
                'transaction_date' => now(),
                'customer_id' => $validated['customer_id'],
                'service_id' => $validated['service_id'],
                'quantity' => $validated['quantity'],
                'price' => $service->price,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cash' ? 'paid' : 'pending',
                'notes' => $validated['notes'] ?? null,
            ];

            if ($validated['payment_method'] !== 'cash') {
                $transactionData['midtrans_order_id'] = 'ORDER-' . Str::uuid();
            }

            $transaction = Transaction::create($transactionData);

            if ($validated['payment_method'] === 'midtrans') {
                $midtransResponse = $this->midtransService->createTransaction($transaction);

                if (!$midtransResponse['success']) {
                    throw new \Exception($midtransResponse['message'] ?? 'Gagal generate token Midtrans');
                }

                $transaction->update([
                    'midtrans_payment_url' => $midtransResponse['payment_url'] ?? null,
                    'midtrans_snap_token' => $midtransResponse['snap_token'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('transactions.payment', $transaction)
                ->with('success', 'Transaksi berhasil dibuat! Silakan lakukan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat transaksi: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['customer', 'service']);
        return view('transactions.show', compact('transaction'));
    }

    public function payment(Transaction $transaction)
    {
        if ($transaction->payment_method !== 'midtrans' || $transaction->payment_status !== 'pending') {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'Transaksi ini tidak dapat dibayar dengan Midtrans.');
        }

        // Regenerate token jika hilang
        if (empty($transaction->midtrans_snap_token)) {
            $response = $this->midtransService->createTransaction($transaction);
            if ($response['success']) {
                $transaction->update(['midtrans_snap_token' => $response['snap_token']]);
            }
        }

        return view('transactions.payment', compact('transaction'));
    }

    // Optional: webhook handler
    public function handleMidtransNotification(Request $request)
    {
        $result = $this->midtransService->handleNotification();
        return response()->json($result);
    }
}
