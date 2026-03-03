<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'wallet_number', 'wallet_name', 'balance',
        'locked_balance', 'status', 'pin', 'pin_set',
        'daily_transfer_limit', 'daily_topup_limit', 'last_transaction_at',
    ];

    protected $hidden = ['pin'];

    protected $casts = [
        'balance'              => 'decimal:2',
        'locked_balance'       => 'decimal:2',
        'daily_transfer_limit' => 'decimal:2',
        'daily_topup_limit'    => 'decimal:2',
        'pin_set'              => 'boolean',
        'last_transaction_at'  => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(Transaction::class)->orderBy('created_at', 'desc'); }
    public function topUpRequests() { return $this->hasMany(TopUpRequest::class)->orderBy('created_at', 'desc'); }
    public function sentTransfers() { return $this->hasMany(TransferRequest::class, 'sender_wallet_id')->orderBy('created_at', 'desc'); }
    public function receivedTransfers() { return $this->hasMany(TransferRequest::class, 'receiver_wallet_id')->orderBy('created_at', 'desc'); }
    public function scopeActive($query) { return $query->where('status', 'active'); }

    public static function generateWalletNumber(): string
    {
        do {
            $number = 'KP-' . date('Y') . '-' . strtoupper(Str::random(5));
        } while (self::where('wallet_number', $number)->exists());
        return $number;
    }

    public function hasSufficientBalance(float $amount): bool { return $this->balance >= $amount; }
    public function isActive(): bool { return $this->status === 'active'; }
    public function getAvailableBalanceAttribute(): float { return (float) $this->balance - (float) $this->locked_balance; }

    /**
     * Verifikasi PIN — auto-reset jika PIN di DB bukan format Bcrypt yang valid
     */
    public function verifyPin(string $pin): bool
    {
        if (!$this->pin) return false;

        // Bcrypt hash selalu diawali $2y$, $2a$, atau $2b$
        $isBcrypt = str_starts_with($this->pin, '$2y$')
                 || str_starts_with($this->pin, '$2a$')
                 || str_starts_with($this->pin, '$2b$');

        if (!$isBcrypt) {
            // Reset PIN agar user bisa buat ulang
            $this->update(['pin' => null, 'pin_set' => false]);
            return false;
        }

        return Hash::check($pin, $this->pin);
    }
}