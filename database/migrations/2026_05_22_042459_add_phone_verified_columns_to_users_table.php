<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom verifikasi HP ke tabel users yang sudah ada
        Schema::table('users', function (Blueprint $table) {
            // Letakkan setelah kolom 'phone' yang sudah ada
            $table->boolean('is_phone_verified')
                ->default(false)
                ->after('phone')
                ->comment('true jika nomor HP sudah diverifikasi via OTP');

            $table->timestamp('phone_verified_at')
                ->nullable()
                ->after('is_phone_verified')
                ->comment('Waktu nomor HP berhasil diverifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_phone_verified', 'phone_verified_at']);
        });
    }
};