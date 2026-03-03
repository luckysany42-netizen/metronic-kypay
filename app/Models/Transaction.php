<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'type',
        'wallet_id',
        'related_wallet_id',
        'amount',
        'balance_before',
        'balance_after',
        'fee',
        'status',
        'description',
        'note',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
        'fee'            => 'decimal:2',
    ];

    // -------------------------
    // Relationships
    // -------------------------

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function relatedWallet()
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }

    // -------------------------
    // Scopes
    // -------------------------

    public function scopeTopUps($query)
    {
        return $query->where('type', 'top_up');
    }

    public function scopeTransfers($query)
    {
        return $query->whereIn('type', ['transfer_in', 'transfer_out']);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    // -------------------------
    // Helper Methods
    // -------------------------

    /**
     * Generate nomor transaksi unik: TRX-YYYYMMDD-XXXXX
     */
    public static function generateTransactionNumber(): string
    {
        do {
            $number = 'TRX-' . date('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('transaction_number', $number)->exists());

        return $number;
    }

    /**
     * Label tipe transaksi dalam Bahasa Indonesia
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'top_up'       => 'Top Up',
            'transfer_in'  => 'Transfer Masuk',
            'transfer_out' => 'Transfer Keluar',
            default        => $this->type,
        };
    }

    /**
     * Apakah transaksi ini kredit (saldo bertambah)?
     */
    public function isCredit(): bool
    {
        return in_array($this->type, ['top_up', 'transfer_in']);
    }

    /**
     * Apakah transaksi ini debit (saldo berkurang)?
     */
    public function isDebit(): bool
    {
        return $this->type === 'transfer_out';
    }
}