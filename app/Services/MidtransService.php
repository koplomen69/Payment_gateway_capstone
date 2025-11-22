<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Midtrans transaction and get payment URL/Snap token
     */
    public function createTransaction($transaction)
    {
        // Pastikan amount dalam integer (Midtrans requirement)
        $grossAmount = (int) round($transaction->total_amount);

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->midtrans_order_id,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [
                [
                    'id' => $transaction->service->id,
                    'price' => (int) round($transaction->price),
                    'quantity' => (float) $transaction->quantity,
                    'name' => substr($transaction->service->name, 0, 50), // Max 50 chars
                    'category' => 'Laundry Service',
                ]
            ],
            'customer_details' => [
                'first_name' => substr($transaction->customer->name ?? 'Pelanggan', 0, 255),
                'email' => $transaction->customer->email ?? 'customer@example.com',
                'phone' => $transaction->customer->phone ?? '000000000000',
            ],
            'expiry' => [
                'start_time' => date('Y-m-d H:i:s O'),
                'unit' => 'hours',
                'duration' => 24, // Expire dalam 24 jam
            ],
            'callbacks' => [
                'finish' => route('transactions.show', $transaction),
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'payment_url' => "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}"
            ];
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle Midtrans notification (webhook)
     */
    public function handleNotification()
    {
        try {
            $notification = new Notification();

            $transaction = \App\Models\Transaction::where('midtrans_order_id', $notification->order_id)->first();

            if (!$transaction) {
                return ['success' => false, 'message' => 'Transaction not found'];
            }

            $status = $notification->transaction_status;
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
                'midtrans_payment_type' => $notification->payment_type,
                'midtrans_transaction_id' => $notification->transaction_id,
            ]);

            return ['success' => true, 'message' => 'Notification handled successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check transaction status
     */
    public function checkStatus($orderId)
    {
        try {
            $status = \Midtrans\Transaction::status($orderId);
            return ['success' => true, 'status' => $status];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
