<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained('merchants')
                ->onDelete('cascade')
                ->comment('Relasi ke merchants');

            $table->string('code')->comment('Kode produk dari provider, e.g. ML-86, TSEL-10K');
            $table->string('name')->comment('Nama tampil produk, e.g. 86 Diamond, Pulsa 10.000');
            $table->text('description')->nullable()->comment('Deskripsi paket, e.g. Masa aktif 30 hari');
            $table->string('validity')->nullable()->comment('Masa berlaku, e.g. 30 Hari, Seumur hidup');

            // Harga — semua dalam Rupiah (desimal 2 angka)
            $table->decimal('base_price', 12, 2)->comment('Harga dasar dari provider');
            $table->decimal('selling_price', 12, 2)->comment('Harga jual ke pengguna (sudah termasuk margin)');
            $table->decimal('admin_fee', 10, 2)->default(0)->comment('Biaya admin per transaksi');

            // Metadata
            $table->string('category_tag')->nullable()
                ->comment('Tag sub-kategori, e.g. weekly, monthly (untuk filter UI)');
            $table->boolean('is_available')->default(true)
                ->comment('false = produk habis/sedang tidak tersedia');
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['merchant_id', 'code']);
            $table->index(['merchant_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_products');
    }
};