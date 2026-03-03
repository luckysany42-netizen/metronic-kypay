<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * KyPay - Transfer Requests Table
     * Mencatat pengajuan transfer dari satu user ke user lain.
     * Transfer bisa langsung (instant) atau perlu konfirmasi PIN.
     * Admin bisa melihat semua history transfer di sini.
     */
    public function up(): void
    {
        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->id();

            // Nomor referensi unik
            $table->string('reference_number', 30)->unique(); // e.g. TRF-20260225-00001

            // Pengirim
            $table->foreignId('sender_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_wallet_id')->constrained('wallets')->onDelete('cascade');

            // Penerima
            $table->foreignId('receiver_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_wallet_id')->constrained('wallets')->onDelete('cascade');

            // Detail Transfer
            $table->decimal('amount', 15, 2);              // jumlah yang ditransfer
            $table->decimal('fee', 15, 2)->default(0.00);  // biaya transfer (0 untuk sekarang)
            $table->decimal('total_deducted', 15, 2);      // total yang dipotong dari pengirim (amount + fee)
            $table->text('note')->nullable();               // catatan dari pengirim ke penerima

            // Status Transfer
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            // pending   = sedang diproses
            // success   = berhasil, saldo sudah berpindah
            // failed    = gagal (saldo tidak cukup, wallet suspended, dll)
            // cancelled = dibatalkan oleh user sebelum diproses

            // Alasan gagal / batal (untuk debugging dan transparansi ke user)
            $table->string('failure_reason')->nullable();

            // Relasi ke transactions (2 record: satu untuk pengirim, satu untuk penerima)
            $table->foreignId('sender_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('receiver_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();

            // Waktu diproses
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Index
            $table->index('sender_user_id');
            $table->index('receiver_user_id');
            $table->index('sender_wallet_id');
            $table->index('receiver_wallet_id');
            $table->index('status');
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_requests');
    }
};