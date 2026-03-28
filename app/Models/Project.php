<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectItem; // Pastikan import model ProjectItem

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    
    protected $fillable = [
        'user_id',
        'customer_name',
        'address',
        'phone_number',      // ← BARU
        'status',
        'installation_date',
        'notes'
    ];

    protected $casts = [
        'installation_date' => 'date:Y-m-d',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // Relasi ke ProjectItem
    public function items()
    {
        return $this->hasMany(ProjectItem::class, 'project_id');
    }

    // User relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper untuk total harga semua items
    public function getTotalPriceAttribute()
    {
        return $this->items->sum('subtotal');
    }
}