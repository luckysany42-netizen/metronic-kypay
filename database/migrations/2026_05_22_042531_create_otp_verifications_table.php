<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();

            // Nomor HP tujuan OTP — tidak pakai foreign key ke users
            // karena OTP dikirim sebelum user ter-create penuh
            $table->string('phone', 20)
                ->comment('Nomor HP tujuan, format: 08xx atau +628xx');

            $table->string('code', 6)
                ->comment('Kode OTP 6 digit');

            $table->enum('purpose', [
                'register',         // Verifikasi saat daftar akun baru
                'reset_pin',        // Reset PIN transaksi
                'change_phone',     // Ganti nomor HP
            ])->default('register')
                ->comment('Tujuan pengiriman OTP');

            $table->boolean('is_used')
                ->default(false)
                ->comment('true jika OTP sudah dipakai — tidak bisa dipakai lagi');

            $table->timestamp('expired_at')
                ->comment('OTP kedaluwarsa setelah 5 menit dari created_at');

            $table->timestamp('verified_at')
                ->nullable()
                ->comment('Waktu OTP berhasil diverifikasi');

            // Keamanan: catat percobaan salah
            $table->unsignedTinyInteger('attempt_count')
                ->default(0)
                ->comment('Jumlah percobaan verifikasi yang salah, max 5x');

            $table->string('ip_address', 45)
                ->nullable()
                ->comment('IP pengirim request, untuk rate limiting');

            $table->timestamps();

            // Index untuk query yang sering dipakai
            $table->index(['phone', 'purpose']);
            $table->index(['phone', 'code', 'is_used']);
            $table->index('expired_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};