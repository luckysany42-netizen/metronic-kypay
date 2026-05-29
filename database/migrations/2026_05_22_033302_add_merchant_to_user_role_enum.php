<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum role dari ('admin','user') menjadi ('admin','user','merchant')
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user','merchant') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        // Kembalikan ke enum semula — pastikan tidak ada data merchant sebelum rollback
        DB::statement("UPDATE users SET role = 'user' WHERE role = 'merchant'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user'");
    }
};