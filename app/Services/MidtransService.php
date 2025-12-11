<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected $serverKey;
    protected $isProduction;
    protected $baseUrl;

    public function __construct()
    {
        $this->serverKey = env('MIDTRANS_SERVER_KEY');
        $this->isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
        $this->baseUrl = $this->isProduction ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
    }

    public function createTransaction(Transaction $transaction)
    {
        try {
            $snapUrl = $this->isProduction
                ? 'https://app.midtrans.com/snap/v1/transactions'
                : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

            $payload = [
                'transaction_details' => [
                    'order_id' => $transaction->midtrans_order_id,
                    'gross_amount' => (int) $transaction->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $transaction->customer->name ?? 'Customer',
                    'phone' => $transaction->customer->phone ?? '',
                ],
            ];

            $response = Http::withBasicAuth($this->serverKey, '')->post($snapUrl, $payload);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'snap_token' => $body['token'] ?? null,
                    'payment_url' => $body['redirect_url'] ?? $body['payment_url'] ?? null,
                    'response' => $body,
                ];
            }

            return [
                'success' => false,
                'message' => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans createTransaction error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function checkStatus(string $orderId)
    {
        try {
            $url = $this->baseUrl . '/' . $orderId . '/status';
            $response = Http::withBasicAuth($this->serverKey, '')->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans checkStatus error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle incoming Midtrans notification payload (array or Request)
     * Returns array ['success' => bool, 'message' => string]
     */
    public function handleNotification($payload = null)
    {
        try {
            if ($payload instanceof Request) {
                $data = $payload->all();
            } elseif (is_array($payload)) {
                $data = $payload;
            } else {
                $data = request()->all();
            }

            Log::info('MidtransService::handleNotification', $data);

            $orderId = $data['order_id'] ?? null;
            $transactionStatus = $data['transaction_status'] ?? ($data['status_code'] ?? null);
            $paymentType = $data['payment_type'] ?? null;
            $transactionId = $data['transaction_id'] ?? null;

            if (!$orderId) {
                return ['success' => false, 'message' => 'order_id missing'];
            }

            $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

            if (!$transaction) {
                return ['success' => false, 'message' => 'Transaction not found'];
            }

            $mapped = match($transactionStatus) {
                'capture', 'settlement' => 'paid',
                'pending' => 'pending',
                'deny', 'cancel', 'failure' => 'failed',
                'expire' => 'expired',
                default => 'pending'
            };

            $transaction->update([
                'midtrans_transaction_status' => $transactionStatus,
                'midtrans_transaction_id' => $transactionId,
                'midtrans_payment_type' => $paymentType,
                'payment_status' => $mapped,
                'transaction_date' => now(),
            ]);

            // Optionally send receipt when paid - keep simple (non-blocking recommended)
            if ($mapped === 'paid') {
                try {
                    // lazy-load fonnte service to avoid hard dependency here
                    $fonnte = app()->make(FonnteService::class);
                    $fonnte->sendResiText($transaction->customer->phone ?? '', $transaction);
                } catch (\Throwable $e) {
                    Log::warning('Failed to send WA after midtrans notification', ['error' => $e->getMessage()]);
                }
            }

            return ['success' => true, 'message' => 'Transaction updated'];
        } catch (\Exception $e) {
            Log::error('MidtransService::handleNotification error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
