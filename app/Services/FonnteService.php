<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $token;
    protected $apiUrl;

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN');
        $this->apiUrl = env('FONNTE_API_URL', 'https://api.fonnte.com/send');
    }

    /**
     * Kirim resi dalam bentuk teks cantik WhatsApp
     *
     * @param string $phone Nomor HP customer (akan dinormalisasi)
     * @param \App\Models\Transaction $transaction Data transaksi
     * @return bool Berhasil atau tidak
     */
    public function sendResiText($phone, $transaction)
    {
        $message = $this->buildResiMessage($transaction);

        try {
            // Use raw token in Authorization header (provider may expect token without 'Bearer ')
            $authHeader = $this->token;

            // Build payload; include token in payload for APIs that expect token param
            $payload = [
                'target'  => $this->normalizePhone($phone),
                'message' => $message,
                'delay'   => '1-3', // Delay 1-3 detik agar tidak kena spam filter
            ];

            // Include raw token in payload (some providers accept token in body)
            if (!empty($this->token)) {
                $payload['token'] = $this->token;
            }

            // Optional sender/device id (if provider requires specifying which WA device to use)
            $sender = env('FONNTE_SENDER');
            if (!empty($sender)) {
                $payload['sender'] = $sender;
            }

            // Mask token in logs to avoid leaking secrets in production logs
            $maskedPayload = $payload;
            if (!empty($maskedPayload['token'])) {
                $tok = $maskedPayload['token'];
                $maskedPayload['token'] = substr($tok, 0, 4) . str_repeat('*', max(3, strlen($tok) - 4));
            }
            Log::debug('FonnteService sendResiText payload', ['url' => $this->apiUrl, 'payload' => $maskedPayload]);

            // Add timeout and retry to be more resilient to transient network issues
            $response = Http::timeout(30)->retry(2, 1000)->withHeaders([
                'Authorization' => $authHeader
            ])->post($this->apiUrl, $payload);

            $responseBody = $response->json();

            // Extra debug info
            Log::debug('FonnteService sendResiText response', [
                'http_status' => $response->status(),
                'body' => $response->body(),
                'json' => $responseBody,
            ]);

            if ($response->successful() && ($responseBody['status'] ?? false) === true) {
                Log::info('Resi WA terkirim sukses', [
                    'phone' => $phone,
                    'invoice' => $transaction->invoice_number,
                    'response' => $responseBody
                ]);
                return true;
            } else {
                Log::warning('Gagal kirim resi WA', [
                    'phone' => $phone,
                    'response' => $responseBody ?? $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error kirim resi WA via Fonnte', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Bangun pesan resi dengan formatting WhatsApp
     *
     * @param \App\Models\Transaction $transaction
     * @return string Pesan siap kirim
     */
    private function buildResiMessage($transaction)
    {
        $customer = $transaction->customer;
        $service  = $transaction->service;

        return "RESI TRANSAKSI LAUNDRY\n\n" .
            "*Invoice:* {$transaction->invoice_number}\n" .
            "*Tanggal:* {$transaction->transaction_date->format('d M Y, H:i')}\n\n" .
            "*Data Pelanggan*\n" .
            "Nama: *{$customer->name}*\n" .
            "No. HP: {$customer->phone}\n" .
            "Alamat: " . ($customer->address ?? '-') . "\n\n" .
            "*Detail Layanan*\n" .
            "Layanan: *{$service->name}*\n" .
            "Jumlah: {$transaction->quantity} {$service->unit}\n" .
            "Harga Satuan: Rp " . number_format($transaction->price, 0, ',', '.') . "\n" .
            "*Total Bayar:* *Rp " . number_format($transaction->total_amount, 0, ',', '.') . "*\n\n" .
            "*Metode Pembayaran:* " . ucfirst($transaction->payment_method) . "\n" .
            "*Status:* PAID\n\n" .
            ($transaction->notes ? "*Catatan:* {$transaction->notes}\n\n" : "") .
            "Terima kasih telah menggunakan layanan kami!\n" .
            "Kami tunggu order berikutnya ya!\n" .
            "Info lebih lanjut: Hubungi kami di WA ini.";
    }

    /**
     * Normalize nomor HP ke format internasional Indonesia (62...)
     *
     * @param string $phone
     * @return string
     */
    private function normalizePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone); // Hapus karakter non-digit
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        return $phone;
    }
}
