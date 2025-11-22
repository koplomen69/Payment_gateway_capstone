<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address'
    ];

    /**
     * Relationship dengan Transaction
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get total transaksi customer
     */
    public function getTotalTransactionsAttribute(): int
    {
        return $this->transactions()->count();
    }

    /**
     * Get total spending customer
     */
    public function getTotalSpendingAttribute(): float
    {
        return $this->transactions()->successful()->sum('total_amount');
    }
}
