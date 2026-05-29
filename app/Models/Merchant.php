<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Merchant extends Model
{
    protected $fillable = [
        'merchant_category_id',
        'code',
        'name',
        'logo_url',
        'input_type',
        'input_label',
        'input_hint',
        'input_prefix',
        'input_min_length',
        'input_max_length',
        'has_inquiry',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'has_inquiry'      => 'boolean',
        'is_active'        => 'boolean',
        'is_featured'      => 'boolean',
        'sort_order'       => 'integer',
        'input_min_length' => 'integer',
        'input_max_length' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(MerchantCategory::class, 'merchant_category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(MerchantProduct::class)->orderBy('sort_order');
    }

    public function availableProducts(): HasMany
    {
        return $this->hasMany(MerchantProduct::class)
            ->where('is_available', true)
            ->orderBy('sort_order');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MerchantTransaction::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Hanya merchant aktif, diurutkan berdasarkan sort_order.
     *
     * Penggunaan: Merchant::active()->get()
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Hanya merchant yang ditandai featured/unggulan.
     *
     * Penggunaan: Merchant::featured()->get()
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order');
    }

    /**
     * Filter merchant berdasarkan kategori.
     *
     * Penggunaan: Merchant::byCategory('game')->get()
     */
    public function scopeByCategory(Builder $query, string $categoryCode): Builder
    {
        return $query->whereHas('category', function (Builder $q) use ($categoryCode) {
            $q->where('code', $categoryCode);
        });
    }

    /**
     * Pencarian merchant berdasarkan nama.
     *
     * Penggunaan: Merchant::search('mobile')->get()
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('name', 'like', '%' . $keyword . '%');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Kembalikan konfigurasi input dalam bentuk array — digunakan Flutter
     * untuk menentukan tampilan form input secara dinamis.
     */
    public function getInputConfigAttribute(): array
    {
        return [
            'type'       => $this->input_type,
            'label'      => $this->input_label,
            'hint'       => $this->input_hint,
            'prefix'     => $this->input_prefix,
            'min_length' => $this->input_min_length,
            'max_length' => $this->input_max_length,
        ];
    }
}