<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_payments', function (Blueprint $table) {
            $table->string('product_code', 100)->nullable()->after('description');
            $table->string('target_number', 50)->nullable()->after('product_code');
        });
    }

    public function down(): void
    {
        Schema::table('qr_payments', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'target_number']);
        });
    }
};