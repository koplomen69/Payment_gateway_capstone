<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'transaction_date',
        'customer_id',
        'service_id',
        'quantity',
        'price',
        'total_amount',
        'payment_method',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_transaction_status',
        'midtrans_payment_url',
        'payment_status',
        'notes'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Generate invoice number dengan format yang lebih sederhana
            if (empty($transaction->invoice_number)) {
                $date = date('Ymd');
                $random = Str::random(6);
                $transaction->invoice_number = "INV-{$date}-{$random}";
            }

            // Set transaction date jika belum di-set
            if (empty($transaction->transaction_date)) {
                $transaction->transaction_date = now();
            }
        });
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isDigitalPayment(): bool
    {
        return in_array($this->payment_method, ['midtrans', 'transfer']);
    }
}
