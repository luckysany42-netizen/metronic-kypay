<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * KyPay - Top Up Requests Table
     * User mengajukan top up → Admin approve/reject → Saldo bertambah
     */
    public function up(): void
    {
        Schema::create('top_up_requests', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');

            // Nomor referensi unik untuk setiap request (untuk tracking)
            $table->string('reference_number', 30)->unique(); // e.g. TOPUP-20260225-00001

            // Detail Top Up
            $table->decimal('amount', 15, 2);              // jumlah yang ingin di-top up
            $table->string('payment_method');              // nama metode pembayaran, e.g. "BCA Transfer", "Mandiri", dll
            $table->string('payment_account')->nullable(); // nomor rekening / nomor HP tujuan
            $table->string('payment_holder')->nullable();  // nama pemilik rekening tujuan

            // Bukti Transfer dari User
            $table->string('proof_image')->nullable();     // path file foto bukti transfer
            $table->text('user_note')->nullable();         // catatan dari user

            // Status Pengajuan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            // pending  = menunggu konfirmasi admin
            // approved = disetujui, saldo sudah ditambahkan
            // rejected = ditolak admin

            // Admin Action
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            // admin yang melakukan review
            $table->timestamp('reviewed_at')->nullable();  // kapan admin melakukan review
            $table->text('admin_note')->nullable();        // catatan dari admin (alasan reject, dll)

            // Jika disetujui, catat transaction_id nya
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();

            $table->timestamps();

            // Index
            $table->index('user_id');
            $table->index('wallet_id');
            $table->index('status');
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_up_requests');
    }
};