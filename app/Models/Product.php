<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_id',        // Tambah ini
        'name',
        'category',         // Tambah ini
        'length_diameter',  // Tambah ini
        'width',            // Tambah ini
        'price_normal',     // Tambah ini
        'price_medium',     // Tambah ini
        'price_high',       // Tambah ini
        'stock',
        'image',
        'description',
    ];
}