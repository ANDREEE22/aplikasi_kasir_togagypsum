<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectItem extends Model
{
    use HasFactory;

    protected $table = 'project_items';
    
    protected $fillable = [
        'project_id',
        'item_name',
        'item_price',
        'quantity',
        'subtotal'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // Relasi ke Project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}