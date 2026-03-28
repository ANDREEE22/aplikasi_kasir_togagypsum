<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'total_normal',
        'total_medium',
        'total_high',
        'status',
        'payment_method',
        'shipping_method',
        'shipping_cost',
        'dp_nominal',
        'recipient_name',
        'customer_name', // ← TAMBAHKAN
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'total_normal' => 'decimal:2',
        'total_medium' => 'decimal:2',
        'total_high' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'dp_nominal' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}