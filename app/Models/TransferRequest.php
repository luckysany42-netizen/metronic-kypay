<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'sender_user_id',
        'sender_wallet_id',
        'receiver_user_id',
        'receiver_wallet_id',
        'amount',
        'fee',
        'total_deducted',
        'note',
        'status',
        'failure_reason',
        'sender_transaction_id',
        'receiver_transaction_id',
        'processed_at',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'fee'           => 'decimal:2',
        'total_deducted'=> 'decimal:2',
        'processed_at'  => 'datetime',
    ];

    // -------------------------
    // Relationships
    // -------------------------

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }

    public function senderWallet()
    {
        return $this->belongsTo(Wallet::class, 'sender_wallet_id');
    }

    public function receiverWallet()
    {
        return $this->belongsTo(Wallet::class, 'receiver_wallet_id');
    }

    public function senderTransaction()
    {
        return $this->belongsTo(Transaction::class, 'sender_transaction_id');
    }

    public function receiverTransaction()
    {
        return $this->belongsTo(Transaction::class, 'receiver_transaction_id');
    }

    // -------------------------
    // Scopes
    // -------------------------

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('sender_user_id', $userId)
                     ->orWhere('receiver_user_id', $userId);
    }

    // -------------------------
    // Helper Methods
    // -------------------------

    /**
     * Generate nomor referensi transfer: TRF-YYYYMMDD-XXXXX
     */
    public static function generateReferenceNumber(): string
    {
        do {
            $number = 'TRF-' . date('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('reference_number', $number)->exists());

        return $number;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Diproses',
            'success'   => 'Berhasil',
            'failed'    => 'Gagal',
            'cancelled' => 'Dibatalkan',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'success'   => 'success',
            'failed'    => 'danger',
            'cancelled' => 'secondary',
            default     => 'secondary',
        };
    }
}