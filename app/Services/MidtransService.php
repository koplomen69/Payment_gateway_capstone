<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

    /**
     * Create a transaction on Midtrans (Snap) and return payment url / token
     * Returns array with keys: success (bool), payment_url|null, token|null, message on error
     */
    public function createTransaction($transaction)
    {
        try {
            $snapUrl = $this->isProduction ? 'https://app.midtrans.com/snap/v1/transactions' : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

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
                $paymentUrl = $body['redirect_url'] ?? $body['payment_url'] ?? null;
                $token = $body['token'] ?? null;

                return [
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'token' => $token,
                    'response' => $body,
                ];
            }

            return [
                'success' => false,
                'message' => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check transaction status using Midtrans Core API
     */
    public function checkStatus($orderId)
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
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
