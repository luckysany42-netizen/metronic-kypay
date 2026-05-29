<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_category_id')
                ->constrained('merchant_categories')
                ->onDelete('restrict')
                ->comment('Relasi ke merchant_categories');

            $table->string('code')->unique()->comment('Kode unik merchant, e.g. ML, FF, PLN, TELKOMSEL');
            $table->string('name')->comment('Nama merchant, e.g. Mobile Legends, PLN Prabayar');
            $table->string('logo_url')->nullable()->comment('URL atau path asset logo merchant');

            // Konfigurasi form input Flutter
            $table->enum('input_type', [
                'phone_number',     // Pulsa, Data, BPJS
                'game_id',          // Mobile Legends, Free Fire
                'customer_number',  // PLN, PDAM
                'account_number',   // Internet, TV Kabel
            ])->comment('Menentukan jenis input form di Flutter');

            $table->string('input_label')->comment('Label field input, e.g. Nomor HP, ID Game');
            $table->string('input_hint')->comment('Placeholder input, e.g. 08xx..., Masukkan ID Game');
            $table->string('input_prefix')->nullable()->comment('Prefix tetap, e.g. 62 untuk nomor internasional');
            $table->unsignedTinyInteger('input_min_length')->default(5);
            $table->unsignedTinyInteger('input_max_length')->default(20);

            // Fitur tambahan
            $table->boolean('has_inquiry')->default(false)
                ->comment('true jika perlu cek tagihan dulu, e.g. PLN pascabayar, PDAM');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false)
                ->comment('Tampil di bagian populer/unggulan di Home');
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('merchant_category_id');
            $table->index(['is_active', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};