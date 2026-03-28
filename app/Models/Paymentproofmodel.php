<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    protected $fillable = [
        'transaction_id',
        'payment_method',
        'file_name',
        'file_path',
        'file_url',
        'file_size',
        'mime_type',
        'description',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'integer',
    ];

    // ===== RELATIONS =====
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}