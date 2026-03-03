<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * KyPay - Payment Methods Table
     * Admin mengelola daftar metode pembayaran untuk Top Up.
     * Contoh: BCA Transfer, Mandiri Transfer, Dana, OVO, dll.
     * User memilih salah satu saat mengajukan top up.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            // Info Metode Pembayaran
            $table->string('name');                        // e.g. "BCA Transfer", "Mandiri", "Dana"
            $table->string('code', 20)->unique();          // e.g. "bca", "mandiri", "dana" (untuk internal)
            $table->enum('type', ['bank_transfer', 'e_wallet', 'other'])->default('bank_transfer');

            // Informasi Rekening / Akun Tujuan (yang user transfer uangnya ke sini)
            $table->string('account_number')->nullable();  // nomor rekening / HP
            $table->string('account_name')->nullable();    // nama pemilik rekening
            $table->string('account_bank')->nullable();    // nama bank (jika bank transfer)

            // Tampilan di Frontend
            $table->string('logo')->nullable();            // path logo/icon metode pembayaran
            $table->text('instructions')->nullable();      // instruksi cara top up menggunakan metode ini
            $table->string('color', 10)->nullable();       // warna hex untuk UI, e.g. "#0066AE" (BCA biru)

            // Batas top up via metode ini
            $table->decimal('min_amount', 15, 2)->default(10000.00);    // minimum top up 10rb
            $table->decimal('max_amount', 15, 2)->default(50000000.00); // maksimum top up 50jt

            // Status
            $table->boolean('is_active')->default(true);   // aktif/nonaktif oleh admin
            $table->integer('sort_order')->default(0);     // urutan tampil di frontend

            // Dibuat oleh admin
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('is_active');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};