<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LonjaranItem extends Model
{
    use HasFactory;

    protected $table = 'lonjaran_items';
    protected $guarded = ['id'];
    
    protected $fillable = [
        'lonjaran_id',
        'item_name',
        'item_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'item_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // Relasi ke Lonjaran
    public function lonjaran()
    {
        return $this->belongsTo(Lonjaran::class);
    }
}