<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MerchantCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'icon_url',
        'color_hex',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    /**
     * Semua merchant yang berada dalam kategori ini.
     */
    public function merchants(): HasMany
    {
        return $this->hasMany(Merchant::class, 'merchant_category_id');
    }

    /**
     * Hanya merchant yang aktif dalam kategori ini.
     */
    public function activeMerchants(): HasMany
    {
        return $this->hasMany(Merchant::class, 'merchant_category_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Hanya kategori yang aktif, diurutkan berdasarkan sort_order.
     *
     * Penggunaan: MerchantCategory::active()->get()
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}