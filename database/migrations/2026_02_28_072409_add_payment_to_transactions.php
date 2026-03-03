<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter enum type di transactions — tambah 'payment'
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('top_up','transfer_in','transfer_out','payment') NOT NULL");

        // 2. Buat tabel bill_products (data dummy produk pembayaran)
        Schema::create('bill_products', function (Blueprint $table) {
            $table->id();
            $table->string('category');        // pulsa, paket_data, token_listrik, bpjs, voucher_game
            $table->string('provider');        // Telkomsel, Indosat, PLN, dll
            $table->string('name');            // Paket 5GB 30 Hari
            $table->string('code');            // kode unik produk
            $table->decimal('price', 15, 2);   // harga
            $table->string('description')->nullable();
            $table->string('icon')->nullable(); // nama icon bootstrap
            $table->string('color')->nullable(); // warna hex
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Seed data dummy
        $products = [
            // ── PULSA ──
            ['category'=>'pulsa','provider'=>'Telkomsel','name'=>'Pulsa Rp 10.000','code'=>'TKS-PLN-10',  'price'=>10000,  'description'=>'Pulsa Telkomsel 10.000','icon'=>'bi-phone-fill','color'=>'#e74c3c','sort_order'=>1],
            ['category'=>'pulsa','provider'=>'Telkomsel','name'=>'Pulsa Rp 25.000','code'=>'TKS-PLN-25',  'price'=>25000,  'description'=>'Pulsa Telkomsel 25.000','icon'=>'bi-phone-fill','color'=>'#e74c3c','sort_order'=>2],
            ['category'=>'pulsa','provider'=>'Telkomsel','name'=>'Pulsa Rp 50.000','code'=>'TKS-PLN-50',  'price'=>50000,  'description'=>'Pulsa Telkomsel 50.000','icon'=>'bi-phone-fill','color'=>'#e74c3c','sort_order'=>3],
            ['category'=>'pulsa','provider'=>'Indosat',  'name'=>'Pulsa Rp 10.000','code'=>'IND-PLN-10',  'price'=>10000,  'description'=>'Pulsa Indosat 10.000',  'icon'=>'bi-phone-fill','color'=>'#f39c12','sort_order'=>4],
            ['category'=>'pulsa','provider'=>'Indosat',  'name'=>'Pulsa Rp 25.000','code'=>'IND-PLN-25',  'price'=>25000,  'description'=>'Pulsa Indosat 25.000',  'icon'=>'bi-phone-fill','color'=>'#f39c12','sort_order'=>5],
            ['category'=>'pulsa','provider'=>'XL',       'name'=>'Pulsa Rp 10.000','code'=>'XL-PLN-10',   'price'=>10000,  'description'=>'Pulsa XL 10.000',       'icon'=>'bi-phone-fill','color'=>'#3498db','sort_order'=>6],
            ['category'=>'pulsa','provider'=>'XL',       'name'=>'Pulsa Rp 25.000','code'=>'XL-PLN-25',   'price'=>25000,  'description'=>'Pulsa XL 25.000',       'icon'=>'bi-phone-fill','color'=>'#3498db','sort_order'=>7],

            // ── PAKET DATA ──
            ['category'=>'paket_data','provider'=>'Telkomsel','name'=>'1GB / 3 Hari',   'code'=>'TKS-DATA-1G3D', 'price'=>12000,  'description'=>'Internet 1GB berlaku 3 hari',    'icon'=>'bi-wifi','color'=>'#e74c3c','sort_order'=>1],
            ['category'=>'paket_data','provider'=>'Telkomsel','name'=>'5GB / 30 Hari',  'code'=>'TKS-DATA-5G30', 'price'=>50000,  'description'=>'Internet 5GB berlaku 30 hari',   'icon'=>'bi-wifi','color'=>'#e74c3c','sort_order'=>2],
            ['category'=>'paket_data','provider'=>'Telkomsel','name'=>'15GB / 30 Hari', 'code'=>'TKS-DATA-15G30','price'=>100000, 'description'=>'Internet 15GB berlaku 30 hari',  'icon'=>'bi-wifi','color'=>'#e74c3c','sort_order'=>3],
            ['category'=>'paket_data','provider'=>'Indosat',  'name'=>'2GB / 7 Hari',   'code'=>'IND-DATA-2G7D', 'price'=>15000,  'description'=>'Internet 2GB berlaku 7 hari',    'icon'=>'bi-wifi','color'=>'#f39c12','sort_order'=>4],
            ['category'=>'paket_data','provider'=>'Indosat',  'name'=>'8GB / 30 Hari',  'code'=>'IND-DATA-8G30', 'price'=>55000,  'description'=>'Internet 8GB berlaku 30 hari',   'icon'=>'bi-wifi','color'=>'#f39c12','sort_order'=>5],
            ['category'=>'paket_data','provider'=>'XL',       'name'=>'3GB / 7 Hari',   'code'=>'XL-DATA-3G7D',  'price'=>20000,  'description'=>'Internet 3GB berlaku 7 hari',    'icon'=>'bi-wifi','color'=>'#3498db','sort_order'=>6],
            ['category'=>'paket_data','provider'=>'XL',       'name'=>'12GB / 30 Hari', 'code'=>'XL-DATA-12G30', 'price'=>75000,  'description'=>'Internet 12GB berlaku 30 hari',  'icon'=>'bi-wifi','color'=>'#3498db','sort_order'=>7],

            // ── TOKEN LISTRIK ──
            ['category'=>'token_listrik','provider'=>'PLN','name'=>'Token Rp 20.000', 'code'=>'PLN-TOKEN-20',  'price'=>21500,  'description'=>'Token listrik PLN 20.000 + admin 1.500', 'icon'=>'bi-lightning-fill','color'=>'#f1c40f','sort_order'=>1],
            ['category'=>'token_listrik','provider'=>'PLN','name'=>'Token Rp 50.000', 'code'=>'PLN-TOKEN-50',  'price'=>51500,  'description'=>'Token listrik PLN 50.000 + admin 1.500', 'icon'=>'bi-lightning-fill','color'=>'#f1c40f','sort_order'=>2],
            ['category'=>'token_listrik','provider'=>'PLN','name'=>'Token Rp 100.000','code'=>'PLN-TOKEN-100', 'price'=>101500, 'description'=>'Token listrik PLN 100.000 + admin 1.500','icon'=>'bi-lightning-fill','color'=>'#f1c40f','sort_order'=>3],
            ['category'=>'token_listrik','provider'=>'PLN','name'=>'Token Rp 200.000','code'=>'PLN-TOKEN-200', 'price'=>201500, 'description'=>'Token listrik PLN 200.000 + admin 1.500','icon'=>'bi-lightning-fill','color'=>'#f1c40f','sort_order'=>4],

            // ── BPJS ──
            ['category'=>'bpjs','provider'=>'BPJS Kesehatan','name'=>'1 Peserta / 1 Bulan', 'code'=>'BPJS-1P-1B', 'price'=>35000,  'description'=>'Iuran BPJS Kesehatan 1 peserta 1 bulan', 'icon'=>'bi-heart-pulse-fill','color'=>'#27ae60','sort_order'=>1],
            ['category'=>'bpjs','provider'=>'BPJS Kesehatan','name'=>'2 Peserta / 1 Bulan', 'code'=>'BPJS-2P-1B', 'price'=>70000,  'description'=>'Iuran BPJS Kesehatan 2 peserta 1 bulan', 'icon'=>'bi-heart-pulse-fill','color'=>'#27ae60','sort_order'=>2],
            ['category'=>'bpjs','provider'=>'BPJS Kesehatan','name'=>'1 Peserta / 3 Bulan', 'code'=>'BPJS-1P-3B', 'price'=>105000, 'description'=>'Iuran BPJS Kesehatan 1 peserta 3 bulan', 'icon'=>'bi-heart-pulse-fill','color'=>'#27ae60','sort_order'=>3],
            ['category'=>'bpjs','provider'=>'BPJS Kesehatan','name'=>'2 Peserta / 3 Bulan', 'code'=>'BPJS-2P-3B', 'price'=>210000, 'description'=>'Iuran BPJS Kesehatan 2 peserta 3 bulan', 'icon'=>'bi-heart-pulse-fill','color'=>'#27ae60','sort_order'=>4],

            // ── VOUCHER GAME ──
            ['category'=>'voucher_game','provider'=>'Mobile Legends','name'=>'86 Diamonds',   'code'=>'ML-86DM',   'price'=>20000,  'description'=>'86 Diamond Mobile Legends Bang Bang', 'icon'=>'bi-controller','color'=>'#9b59b6','sort_order'=>1],
            ['category'=>'voucher_game','provider'=>'Mobile Legends','name'=>'172 Diamonds',  'code'=>'ML-172DM',  'price'=>40000,  'description'=>'172 Diamond Mobile Legends Bang Bang','icon'=>'bi-controller','color'=>'#9b59b6','sort_order'=>2],
            ['category'=>'voucher_game','provider'=>'Mobile Legends','name'=>'257 Diamonds',  'code'=>'ML-257DM',  'price'=>60000,  'description'=>'257 Diamond Mobile Legends Bang Bang','icon'=>'bi-controller','color'=>'#9b59b6','sort_order'=>3],
            ['category'=>'voucher_game','provider'=>'Free Fire',     'name'=>'70 Diamonds',   'code'=>'FF-70DM',   'price'=>15000,  'description'=>'70 Diamond Free Fire Garena',          'icon'=>'bi-controller','color'=>'#e67e22','sort_order'=>4],
            ['category'=>'voucher_game','provider'=>'Free Fire',     'name'=>'140 Diamonds',  'code'=>'FF-140DM',  'price'=>30000,  'description'=>'140 Diamond Free Fire Garena',         'icon'=>'bi-controller','color'=>'#e67e22','sort_order'=>5],
            ['category'=>'voucher_game','provider'=>'PUBG Mobile',   'name'=>'60 UC',         'code'=>'PUBG-60UC', 'price'=>15000,  'description'=>'60 UC PUBG Mobile',                    'icon'=>'bi-controller','color'=>'#1abc9c','sort_order'=>6],
            ['category'=>'voucher_game','provider'=>'PUBG Mobile',   'name'=>'325 UC',        'code'=>'PUBG-325UC','price'=>75000,  'description'=>'325 UC PUBG Mobile',                   'icon'=>'bi-controller','color'=>'#1abc9c','sort_order'=>7],
        ];

        foreach ($products as $product) {
            DB::table('bill_products')->insert(array_merge($product, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_products');
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('top_up','transfer_in','transfer_out') NOT NULL");
    }
};