<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'order_id',
        'order_code',
        'payment_method',
        'transaction_id',
        'file_name',
        'file_path',
        'file_url',
        'file_size',
        'mime_type',
        'original_name',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'amount',
        'uploaded_at',
        'uploader_ip',
        'uploader_user_agent',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'uploaded_at' => 'datetime',
        'file_size' => 'integer',
        'amount' => 'decimal:2',
    ];

    // ===== RELATIONS =====
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ===== SCOPES =====
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ===== METHODS =====
    public function verify(User $admin, ?string $notes = null): void
    {
        $this->update([
            'status' => 'verified',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    public function reject(User $admin, string $notes): void
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}