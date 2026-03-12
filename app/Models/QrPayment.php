<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QrPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'qr_token',
        'merchant_id',
        'payer_id',
        'transaction_id',
        'amount',
        'description',
        'product_code',
        'target_number',
        'status',
        'expires_at',
        'paid_at',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at'    => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────
    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // ── Helpers ────────────────────────────────────────────
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}