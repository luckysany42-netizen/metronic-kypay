<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * KyPay - Wallet Table
     * Menyimpan saldo dan info dompet digital tiap user
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Identitas Wallet
            $table->string('wallet_number', 20)->unique(); // nomor unik wallet, e.g. KP-20260001
            $table->string('wallet_name')->nullable();     // nama alias wallet (opsional)

            // Saldo - pakai decimal untuk menghindari floating point error
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('locked_balance', 15, 2)->default(0.00); // saldo yang sedang "tertahan" (pending transfer)

            // Status Wallet
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            // active    = normal bisa transaksi
            // suspended = dibekukan admin, tidak bisa transaksi
            // closed    = ditutup permanen

            // PIN untuk otorisasi transaksi (hashed)
            $table->string('pin')->nullable(); // bcrypt hash dari 6 digit PIN
            $table->boolean('pin_set')->default(false);

            // Limit transaksi harian (bisa diset admin per user)
            $table->decimal('daily_transfer_limit', 15, 2)->default(5000000.00);  // default 5jt/hari
            $table->decimal('daily_topup_limit', 15, 2)->default(10000000.00);    // default 10jt/hari

            // Tracking
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); // untuk keamanan, wallet tidak benar-benar dihapus

            // Index untuk performa query
            $table->index('user_id');
            $table->index('wallet_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};