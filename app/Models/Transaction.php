<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // ← TAMBAHKAN INI
use Illuminate\Database\Eloquent\SoftDeletes; // ← JIKA PAKAI SOFT DELETES

class Transaction extends Model
{
    use HasFactory, SoftDeletes; // ← TAMBAHKAN SoftDeletes JIKA DIPERLUKAN

    protected $fillable = [
        'invoice_number',
        'transaction_date',
        'customer_id',
        'service_id',
        'quantity',
        'price',
        'total_amount',
        'payment_method',
        'payment_status',
        'notes',
        // Midtrans fields
        'midtrans_order_id',
        'midtrans_snap_token',
        'midtrans_payment_url',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_transaction_status'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Auto-generate invoice number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->invoice_number)) {
                $transaction->invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }
}
