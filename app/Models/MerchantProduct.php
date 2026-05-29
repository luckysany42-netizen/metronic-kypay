<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MerchantProduct extends Model
{
    protected $fillable = [
        'merchant_id',
        'code',
        'name',
        'description',
        'validity',
        'base_price',
        'selling_price',
        'admin_fee',
        'category_tag',
        'is_available',
        'sort_order',
    ];

    protected $casts = [
        'base_price'    => 'decimal:2',
        'selling_price' => 'decimal:2',
        'admin_fee'     => 'decimal:2',
        'is_available'  => 'boolean',
        'sort_order'    => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MerchantTransaction::class, 'merchant_product_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Hanya produk yang tersedia.
     *
     * Penggunaan: MerchantProduct::available()->get()
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true)->orderBy('sort_order');
    }

    /**
     * Filter produk berdasarkan tag sub-kategori.
     *
     * Penggunaan: MerchantProduct::byTag('weekly')->get()
     */
    public function scopeByTag(Builder $query, string $tag): Builder
    {
        return $query->where('category_tag', $tag);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Total yang harus dibayar pengguna (selling_price + admin_fee).
     * Accessor ini otomatis tersedia sebagai $product->total_price
     */
    public function getTotalPriceAttribute(): float
    {
        return (float) $this->selling_price + (float) $this->admin_fee;
    }

    /**
     * Margin keuntungan per produk untuk keperluan laporan.
     * Accessor ini otomatis tersedia sebagai $product->margin
     */
    public function getMarginAttribute(): float
    {
        return (float) $this->selling_price - (float) $this->base_price;
    }
}