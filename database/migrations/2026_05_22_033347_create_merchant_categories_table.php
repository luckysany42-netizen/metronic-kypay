<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Slug unik, e.g. game, pulsa, tagihan');
            $table->string('name')->comment('Nama tampilan, e.g. Game, Pulsa & Data');
            $table->string('icon_url')->nullable()->comment('Path asset atau URL ikon kategori');
            $table->string('color_hex', 7)->default('#1A5CDB')->comment('Warna tema kategori untuk UI Flutter');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0)->comment('Urutan tampil di UI');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_categories');
    }
};