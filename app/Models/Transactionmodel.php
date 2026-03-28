<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'total_amount',
        'shipping_cost',
        'dp_nominal',
        'payment_method',
        'shipping_method',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'dp_nominal' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    // ===== RELATIONS =====
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function paymentProof(): HasOne
    {
        return $this->hasOne(PaymentProof::class);
    }

    // ===== SCOPES =====
    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ===== METHODS =====
    public function getTotalWithShipping()
    {
        return $this->total_amount + $this->shipping_cost;
    }

    public function getPaymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'transfer' => 'Transfer',
            'dp' => 'DP',
            'bayarTempat' => 'Bayar di Tempat',
            default => $this->payment_method,
        };
    }

    public function getShippingMethodLabel(): string
    {
        return match ($this->shipping_method) {
            'ambil' => 'Ambil di Toko',
            'kirim' => 'Dikirim',
            default => $this->shipping_method,
        };
    }
}