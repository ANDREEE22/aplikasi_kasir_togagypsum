<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    // --- TAMBAHKAN BAGIAN INI ---
    protected $fillable = [
        'order_id',
        'product_id',
        'custom_id',
        'name',
        'category',
        'length_diameter',
        'width',
        'price_normal',
        'price_medium',
        'price_high',
        'qty',
        'price_type',
        'price',
        'subtotal',
    ];
    // ----------------------------
}