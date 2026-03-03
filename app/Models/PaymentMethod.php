<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'account_number',
        'account_name',
        'account_bank',
        'logo',
        'instructions',
        'color',
        'min_amount',
        'max_amount',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // -------------------------
    // Relationships
    // -------------------------

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------
    // Scopes
    // -------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // -------------------------
    // Helper Methods
    // -------------------------

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'bank_transfer' => 'Transfer Bank',
            'e_wallet'      => 'E-Wallet',
            'other'         => 'Lainnya',
            default         => $this->type,
        };
    }
}