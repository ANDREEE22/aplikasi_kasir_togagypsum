<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LonjaranItem;


class Lonjaran extends Model
{
    use HasFactory;

    protected $table = 'lonjaran';
    protected $guarded = ['id'];
    
    protected $fillable = [
        'user_id',
        'customer_name',
        'address',
        'phone_number',
        'entry_date',
        'delivery_deadline',
        'delivery_type',
        'total_price',
        'payment_status',
        'order_status',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date:Y-m-d',
        'delivery_deadline' => 'date:Y-m-d',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Items
    public function lonjaranItems()
    {
        return $this->hasMany(LonjaranItem::class);
    }
}