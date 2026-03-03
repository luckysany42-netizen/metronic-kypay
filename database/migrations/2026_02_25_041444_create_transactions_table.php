<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * KyPay - Transactions Table
     * Tabel utama yang mencatat SEMUA pergerakan uang di KyPay
     * Setiap top up yang diapprove dan setiap transfer akan masuk ke sini
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Nomor transaksi unik yang ditampilkan ke user
            $table->string('transaction_number', 30)->unique(); // e.g. TRX-20260225-00001

            // Jenis transaksi
            $table->enum('type', ['top_up', 'transfer_in', 'transfer_out']);
            // top_up       = penambahan saldo dari top up yang diapprove admin
            // transfer_in  = saldo masuk karena menerima transfer dari user lain
            // transfer_out = saldo keluar karena mengirim transfer ke user lain

            // Wallet yang terlibat
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            // wallet yang mengalami perubahan saldo (pemilik transaksi ini)

            $table->foreignId('related_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            // untuk transfer: wallet lawan transaksi
            // untuk top_up: null (tidak ada wallet lawan)

            // Nominal
            $table->decimal('amount', 15, 2);              // jumlah uang yang berpindah (selalu positif)
            $table->decimal('balance_before', 15, 2);      // saldo wallet SEBELUM transaksi
            $table->decimal('balance_after', 15, 2);       // saldo wallet SETELAH transaksi
            // balance_after = balance_before + amount (jika in) atau balance_before - amount (jika out)

            // Biaya admin/fee (untuk pengembangan ke depan)
            $table->decimal('fee', 15, 2)->default(0.00);  // biaya transaksi jika ada

            // Status transaksi
            $table->enum('status', ['success', 'failed', 'reversed'])->default('success');
            // success  = transaksi berhasil
            // failed   = transaksi gagal
            // reversed = transaksi dibatalkan/dibalik oleh admin

            // Deskripsi / Keterangan
            $table->string('description')->nullable();     // deskripsi otomatis sistem
            $table->text('note')->nullable();              // catatan tambahan dari user

            // Referensi ke request asal (untuk tracing)
            $table->string('reference_type')->nullable();  // 'top_up_request' atau 'transfer_request'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID dari tabel referensi

            $table->timestamps();

            // Index untuk performa
            $table->index('wallet_id');
            $table->index('related_wallet_id');
            $table->index('type');
            $table->index('status');
            $table->index('transaction_number');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};