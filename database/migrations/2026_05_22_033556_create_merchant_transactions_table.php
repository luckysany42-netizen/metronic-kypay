<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_transactions', function (Blueprint $table) {
            $table->id();

            // Relasi utama
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Pengguna yang melakukan transaksi');
            $table->foreignId('merchant_id')
                ->constrained('merchants')
                ->onDelete('restrict');
            $table->foreignId('merchant_product_id')
                ->constrained('merchant_products')
                ->onDelete('restrict');

            // Idempotency — mencegah transaksi ganda
            $table->string('idempotency_key')->unique()
                ->comment('UUID unik per percobaan bayar, dibuat di Flutter sebelum request');

            // Data input dari pengguna
            $table->string('input_value')
                ->comment('Nilai input: nomor HP, ID game, nomor pelanggan, dll.');

            // Snapshot harga saat transaksi (jangan join ke products untuk audit)
            $table->decimal('product_price', 12, 2)->comment('Harga produk saat transaksi');
            $table->decimal('admin_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->comment('Total yang dipotong dari wallet');

            // Status transaksi
            $table->enum('status', [
                'pending',      // Baru dibuat, belum diproses
                'processing',   // Sedang diproses ke provider
                'success',      // Berhasil
                'failed',       // Gagal dari provider
                'refunded',     // Sudah dikembalikan ke wallet
            ])->default('pending');

            // Integrasi wallet — relasi ke tabel wallets yang sudah ada
            $table->foreignId('wallet_id')
                ->nullable()
                ->constrained('wallets')
                ->onDelete('set null')
                ->comment('Wallet yang digunakan untuk pembayaran');
            $table->decimal('balance_before', 12, 2)->nullable()
                ->comment('Saldo sebelum transaksi, untuk audit');
            $table->decimal('balance_after', 12, 2)->nullable()
                ->comment('Saldo setelah transaksi, untuk audit');

            // Data dari provider eksternal
            $table->string('provider_reference')->nullable()
                ->comment('Nomor referensi dari provider/BILLER');
            $table->string('provider_message')->nullable()
                ->comment('Pesan status dari provider');
            $table->json('provider_response')->nullable()
                ->comment('Raw response JSON dari provider, untuk debugging');

            // Data struk
            $table->json('receipt_data')->nullable()
                ->comment('Data lengkap untuk tampilan struk di Flutter');

            // Timestamp tambahan
            $table->timestamp('processed_at')->nullable()
                ->comment('Waktu transaksi selesai diproses provider');
            $table->timestamps();

            // Indexes untuk query yang sering dipakai
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_transactions');
    }
};