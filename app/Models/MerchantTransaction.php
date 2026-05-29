<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MerchantTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'merchant_id',
        'merchant_product_id',
        'idempotency_key',
        'input_value',
        'product_price',
        'admin_fee',
        'total_amount',
        'status',
        'wallet_id',
        'balance_before',
        'balance_after',
        'provider_reference',
        'provider_message',
        'provider_response',
        'receipt_data',
        'processed_at',
    ];

    protected $casts = [
        'product_price'     => 'decimal:2',
        'admin_fee'         => 'decimal:2',
        'total_amount'      => 'decimal:2',
        'balance_before'    => 'decimal:2',
        'balance_after'     => 'decimal:2',
        'provider_response' => 'array',
        'receipt_data'      => 'array',
        'processed_at'      => 'datetime',
    ];

    // Status constants — gunakan ini di seluruh codebase, bukan raw string
    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS    = 'success';
    const STATUS_FAILED     = 'failed';
    const STATUS_REFUNDED   = 'refunded';

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MerchantProduct::class, 'merchant_product_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Filter transaksi berdasarkan status.
     *
     * Penggunaan: MerchantTransaction::byStatus('success')->get()
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Transaksi milik user tertentu, diurutkan terbaru dulu.
     *
     * Penggunaan: MerchantTransaction::forUser($userId)->get()
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->latest();
    }

    /**
     * Transaksi yang masih menggantung / belum selesai.
     *
     * Penggunaan: MerchantTransaction::pending()->get()
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Apakah transaksi ini sudah final (tidak bisa diubah lagi).
     * Accessor: $transaction->is_final
     */
    public function getIsFinalAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUCCESS,
            self::STATUS_FAILED,
            self::STATUS_REFUNDED,
        ]);
    }

    /**
     * Label status dalam Bahasa Indonesia untuk tampilan UI.
     * Accessor: $transaction->status_label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'Menunggu',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_SUCCESS    => 'Berhasil',
            self::STATUS_FAILED     => 'Gagal',
            self::STATUS_REFUNDED   => 'Dikembalikan',
            default                 => 'Tidak Diketahui',
        };
    }

    /**
     * Warna status untuk badge UI (hex color).
     * Accessor: $transaction->status_color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => '#F59E0B',
            self::STATUS_PROCESSING => '#3B82F6',
            self::STATUS_SUCCESS    => '#10B981',
            self::STATUS_FAILED     => '#EF4444',
            self::STATUS_REFUNDED   => '#8B5CF6',
            default                 => '#6B7280',
        };
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Tandai transaksi sebagai berhasil dan catat waktu proses.
     *
     * Penggunaan di PaymentService:
     *   $transaction->markAsSuccess($reference, $receiptData);
     */
    public function markAsSuccess(string $providerReference, array $receiptData = []): bool
    {
        return $this->update([
            'status'             => self::STATUS_SUCCESS,
            'provider_reference' => $providerReference,
            'receipt_data'       => $receiptData,
            'processed_at'       => now(),
        ]);
    }

    /**
     * Tandai transaksi sebagai gagal.
     *
     * Penggunaan di PaymentService:
     *   $transaction->markAsFailed('Saldo tidak cukup');
     */
    public function markAsFailed(string $reason, array $providerResponse = []): bool
    {
        return $this->update([
            'status'            => self::STATUS_FAILED,
            'provider_message'  => $reason,
            'provider_response' => $providerResponse,
            'processed_at'      => now(),
        ]);
    }

    /**
     * Tandai transaksi sebagai refund (setelah gagal & saldo dikembalikan).
     */
    public function markAsRefunded(): bool
    {
        return $this->update([
            'status'       => self::STATUS_REFUNDED,
            'processed_at' => now(),
        ]);
    }
}