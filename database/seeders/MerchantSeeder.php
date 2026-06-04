<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MerchantCategory;
use App\Models\Merchant;
use App\Models\MerchantProduct;

class MerchantSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. KATEGORI ───────────────────────────────────────────────────────
        $categories = [
            ['code' => 'game',      'name' => 'Game',          'color_hex' => '#7C3AED', 'sort_order' => 1],
            ['code' => 'pulsa',     'name' => 'Pulsa & Data',  'color_hex' => '#2563EB', 'sort_order' => 2],
            ['code' => 'tagihan',   'name' => 'Tagihan',       'color_hex' => '#DC2626', 'sort_order' => 3],
            ['code' => 'rumah',     'name' => 'Rumah Tangga',  'color_hex' => '#D97706', 'sort_order' => 4],
            ['code' => 'hiburan',   'name' => 'Hiburan',       'color_hex' => '#059669', 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            MerchantCategory::updateOrCreate(['code' => $cat['code']], array_merge($cat, ['is_active' => true]));
        }

        $game    = MerchantCategory::where('code', 'game')->first();
        $pulsa   = MerchantCategory::where('code', 'pulsa')->first();
        $tagihan = MerchantCategory::where('code', 'tagihan')->first();
        $rumah   = MerchantCategory::where('code', 'rumah')->first();
        $hiburan = MerchantCategory::where('code', 'hiburan')->first();

        // ── 2. MERCHANT & PRODUK ──────────────────────────────────────────────

        // ── GAME ─────────────────────────────────────────────────────────────
        $ml = Merchant::updateOrCreate(['code' => 'ML'], [
            'merchant_category_id' => $game->id,
            'name'             => 'Mobile Legends',
            'input_type'       => 'game_id',
            'input_label'      => 'User ID',
            'input_hint'       => 'Contoh: 123456789 (1234)',
            'input_min_length' => 6,
            'input_max_length' => 12,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => true,
            'sort_order'       => 1,
        ]);
        $this->createProducts($ml->id, [
            ['code' => 'ML-86',   'name' => '86 Diamond',         'base' => 19000,  'sell' => 20500,  'fee' => 1000],
            ['code' => 'ML-172',  'name' => '172 Diamond',        'base' => 38000,  'sell' => 40500,  'fee' => 1000],
            ['code' => 'ML-257',  'name' => '257 Diamond',        'base' => 56000,  'sell' => 59000,  'fee' => 1000],
            ['code' => 'ML-344',  'name' => '344 Diamond',        'base' => 74000,  'sell' => 78000,  'fee' => 1000],
            ['code' => 'ML-514',  'name' => '514 Diamond',        'base' => 110000, 'sell' => 115000, 'fee' => 1000],
            ['code' => 'ML-706',  'name' => '706 Diamond',        'base' => 149000, 'sell' => 156000, 'fee' => 1000],
            ['code' => 'ML-1412', 'name' => '1412 Diamond',       'base' => 296000, 'sell' => 310000, 'fee' => 1000],
            ['code' => 'ML-WP',   'name' => 'Weekly Diamond Pass','base' => 28000,  'sell' => 30000,  'fee' => 1000, 'tag' => 'weekly'],
        ]);

        $ff = Merchant::updateOrCreate(['code' => 'FF'], [
            'merchant_category_id' => $game->id,
            'name'             => 'Free Fire',
            'input_type'       => 'game_id',
            'input_label'      => 'Player ID',
            'input_hint'       => 'Masukkan Player ID Free Fire',
            'input_min_length' => 6,
            'input_max_length' => 12,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => true,
            'sort_order'       => 2,
        ]);
        $this->createProducts($ff->id, [
            ['code' => 'FF-70',   'name' => '70 Diamond',   'base' => 13500,  'sell' => 14500,  'fee' => 1000],
            ['code' => 'FF-140',  'name' => '140 Diamond',  'base' => 26500,  'sell' => 28500,  'fee' => 1000],
            ['code' => 'FF-355',  'name' => '355 Diamond',  'base' => 66000,  'sell' => 70000,  'fee' => 1000],
            ['code' => 'FF-720',  'name' => '720 Diamond',  'base' => 132000, 'sell' => 139000, 'fee' => 1000],
            ['code' => 'FF-1450', 'name' => '1450 Diamond', 'base' => 261000, 'sell' => 275000, 'fee' => 1000],
            ['code' => 'FF-WP',   'name' => 'Weekly Pass',  'base' => 26000,  'sell' => 28000,  'fee' => 1000, 'tag' => 'weekly'],
        ]);

        $pubg = Merchant::updateOrCreate(['code' => 'PUBG'], [
            'merchant_category_id' => $game->id,
            'name'             => 'PUBG Mobile',
            'input_type'       => 'game_id',
            'input_label'      => 'Player ID',
            'input_hint'       => 'Masukkan Player ID PUBG Mobile',
            'input_min_length' => 6,
            'input_max_length' => 12,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => false,
            'sort_order'       => 3,
        ]);
        $this->createProducts($pubg->id, [
            ['code' => 'PUBG-60',   'name' => '60 UC',   'base' => 14000,  'sell' => 15500,  'fee' => 1000],
            ['code' => 'PUBG-325',  'name' => '325 UC',  'base' => 72000,  'sell' => 76000,  'fee' => 1000],
            ['code' => 'PUBG-660',  'name' => '660 UC',  'base' => 143000, 'sell' => 150000, 'fee' => 1000],
            ['code' => 'PUBG-1800', 'name' => '1800 UC', 'base' => 380000, 'sell' => 399000, 'fee' => 1000],
        ]);

        // ── PULSA & DATA ──────────────────────────────────────────────────────
        $tsel = Merchant::updateOrCreate(['code' => 'TELKOMSEL'], [
            'merchant_category_id' => $pulsa->id,
            'name'             => 'Telkomsel',
            'input_type'       => 'phone_number',
            'input_label'      => 'Nomor Telkomsel',
            'input_hint'       => '0812xxxxxxxx',
            'input_prefix'     => '0852, 0853, 0811, 0812, 0813, 0821, 0822, 0823',
            'input_min_length' => 10,
            'input_max_length' => 13,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => true,
            'sort_order'       => 1,
        ]);
        $this->createProducts($tsel->id, [
            ['code' => 'TSEL-5K',  'name' => 'Pulsa 5.000',   'base' => 5500,  'sell' => 6000,  'fee' => 500],
            ['code' => 'TSEL-10K', 'name' => 'Pulsa 10.000',  'base' => 10500, 'sell' => 11000, 'fee' => 500],
            ['code' => 'TSEL-20K', 'name' => 'Pulsa 20.000',  'base' => 20500, 'sell' => 21500, 'fee' => 500],
            ['code' => 'TSEL-50K', 'name' => 'Pulsa 50.000',  'base' => 50500, 'sell' => 52000, 'fee' => 500],
            ['code' => 'TSEL-100K','name' => 'Pulsa 100.000', 'base' => 100500,'sell' => 103000,'fee' => 500],
            ['code' => 'TSEL-D1',  'name' => 'Data 1GB/7 Hari',  'base' => 13000, 'sell' => 14500, 'fee' => 500, 'tag' => 'data', 'validity' => '7 Hari'],
            ['code' => 'TSEL-D2',  'name' => 'Data 2GB/30 Hari', 'base' => 24000, 'sell' => 26000, 'fee' => 500, 'tag' => 'data', 'validity' => '30 Hari'],
            ['code' => 'TSEL-D5',  'name' => 'Data 5GB/30 Hari', 'base' => 48000, 'sell' => 51000, 'fee' => 500, 'tag' => 'data', 'validity' => '30 Hari'],
        ]);

        $indosat = Merchant::updateOrCreate(['code' => 'INDOSAT'], [
            'merchant_category_id' => $pulsa->id,
            'name'             => 'Indosat Ooredoo',
            'input_type'       => 'phone_number',
            'input_label'      => 'Nomor Indosat',
            'input_hint'       => '0814xxxxxxxx',
            'input_min_length' => 10,
            'input_max_length' => 13,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => false,
            'sort_order'       => 2,
        ]);
        $this->createProducts($indosat->id, [
            ['code' => 'IST-10K',  'name' => 'Pulsa 10.000',  'base' => 10500, 'sell' => 11000, 'fee' => 500],
            ['code' => 'IST-25K',  'name' => 'Pulsa 25.000',  'base' => 25500, 'sell' => 26500, 'fee' => 500],
            ['code' => 'IST-50K',  'name' => 'Pulsa 50.000',  'base' => 50500, 'sell' => 52000, 'fee' => 500],
            ['code' => 'IST-100K', 'name' => 'Pulsa 100.000', 'base' => 100500,'sell' => 103000,'fee' => 500],
            ['code' => 'IST-D3',   'name' => 'Data 3GB/30 Hari', 'base' => 28000, 'sell' => 30000, 'fee' => 500, 'tag' => 'data', 'validity' => '30 Hari'],
        ]);

        $xl = Merchant::updateOrCreate(['code' => 'XL'], [
            'merchant_category_id' => $pulsa->id,
            'name'             => 'XL Axiata',
            'input_type'       => 'phone_number',
            'input_label'      => 'Nomor XL',
            'input_hint'       => '0817xxxxxxxx',
            'input_min_length' => 10,
            'input_max_length' => 13,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => false,
            'sort_order'       => 3,
        ]);
        $this->createProducts($xl->id, [
            ['code' => 'XL-10K',  'name' => 'Pulsa 10.000',  'base' => 10500, 'sell' => 11000, 'fee' => 500],
            ['code' => 'XL-50K',  'name' => 'Pulsa 50.000',  'base' => 50500, 'sell' => 52000, 'fee' => 500],
            ['code' => 'XL-100K', 'name' => 'Pulsa 100.000', 'base' => 100500,'sell' => 103000,'fee' => 500],
            ['code' => 'XL-D2',   'name' => 'Data 2GB/30 Hari', 'base' => 22000, 'sell' => 24000, 'fee' => 500, 'tag' => 'data', 'validity' => '30 Hari'],
        ]);

        // ── TAGIHAN ───────────────────────────────────────────────────────────
        $pln = Merchant::updateOrCreate(['code' => 'PLN_POST'], [
            'merchant_category_id' => $tagihan->id,
            'name'             => 'PLN Pascabayar',
            'input_type'       => 'customer_number',
            'input_label'      => 'Nomor Pelanggan PLN',
            'input_hint'       => 'Contoh: 123456789012',
            'input_min_length' => 9,
            'input_max_length' => 12,
            'has_inquiry'      => true, // Cek tagihan dulu
            'is_active'        => true,
            'is_featured'      => true,
            'sort_order'       => 1,
        ]);
        // PLN Pascabayar tidak punya produk fix — tagihannya dari inquiry
        MerchantProduct::updateOrCreate(
            ['merchant_id' => $pln->id, 'code' => 'PLN_POST_PAY'],
            [
                'name'         => 'Bayar Tagihan PLN',
                'description'  => 'Nominal tagihan sesuai hasil inquiry',
                'base_price'   => 0,
                'selling_price'=> 0,
                'admin_fee'    => 2500,
                'is_available' => true,
                'sort_order'   => 1,
            ]
        );

        $bpjs = Merchant::updateOrCreate(['code' => 'BPJS'], [
            'merchant_category_id' => $tagihan->id,
            'name'             => 'BPJS Kesehatan',
            'input_type'       => 'customer_number',
            'input_label'      => 'Nomor Virtual Account / NIK',
            'input_hint'       => 'Contoh: 0001234567890',
            'input_min_length' => 10,
            'input_max_length' => 16,
            'has_inquiry'      => true,
            'is_active'        => true,
            'is_featured'      => true,
            'sort_order'       => 2,
        ]);
        MerchantProduct::updateOrCreate(
            ['merchant_id' => $bpjs->id, 'code' => 'BPJS_PAY'],
            [
                'name'         => 'Bayar BPJS Kesehatan',
                'description'  => 'Nominal tagihan sesuai hasil inquiry',
                'base_price'   => 0,
                'selling_price'=> 0,
                'admin_fee'    => 2500,
                'is_available' => true,
                'sort_order'   => 1,
            ]
        );

        $pdam = Merchant::updateOrCreate(['code' => 'PDAM'], [
            'merchant_category_id' => $tagihan->id,
            'name'             => 'PDAM',
            'input_type'       => 'customer_number',
            'input_label'      => 'Nomor Pelanggan PDAM',
            'input_hint'       => 'Contoh: 123456789',
            'input_min_length' => 6,
            'input_max_length' => 12,
            'has_inquiry'      => true,
            'is_active'        => true,
            'is_featured'      => false,
            'sort_order'       => 3,
        ]);
        MerchantProduct::updateOrCreate(
            ['merchant_id' => $pdam->id, 'code' => 'PDAM_PAY'],
            [
                'name'         => 'Bayar Tagihan PDAM',
                'description'  => 'Nominal tagihan sesuai hasil inquiry',
                'base_price'   => 0,
                'selling_price'=> 0,
                'admin_fee'    => 2500,
                'is_available' => true,
                'sort_order'   => 1,
            ]
        );

        // ── RUMAH TANGGA ──────────────────────────────────────────────────────
        $plnpre = Merchant::updateOrCreate(['code' => 'PLN_PRE'], [
            'merchant_category_id' => $rumah->id,
            'name'             => 'PLN Prabayar (Token)',
            'input_type'       => 'customer_number',
            'input_label'      => 'Nomor Meter / ID Pelanggan',
            'input_hint'       => 'Contoh: 123456789012',
            'input_min_length' => 9,
            'input_max_length' => 12,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => true,
            'sort_order'       => 1,
        ]);
        $this->createProducts($plnpre->id, [
            ['code' => 'PLN-20K',  'name' => 'Token PLN 20.000',  'base' => 20000,  'sell' => 21500,  'fee' => 1500],
            ['code' => 'PLN-50K',  'name' => 'Token PLN 50.000',  'base' => 50000,  'sell' => 52000,  'fee' => 1500],
            ['code' => 'PLN-100K', 'name' => 'Token PLN 100.000', 'base' => 100000, 'sell' => 102500, 'fee' => 1500],
            ['code' => 'PLN-200K', 'name' => 'Token PLN 200.000', 'base' => 200000, 'sell' => 203000, 'fee' => 1500],
            ['code' => 'PLN-500K', 'name' => 'Token PLN 500.000', 'base' => 500000, 'sell' => 504000, 'fee' => 1500],
        ]);

        // ── HIBURAN ───────────────────────────────────────────────────────────
        $spotify = Merchant::updateOrCreate(['code' => 'SPOTIFY'], [
            'merchant_category_id' => $hiburan->id,
            'name'             => 'Spotify',
            'input_type'       => 'account_number',
            'input_label'      => 'Email Akun Spotify',
            'input_hint'       => 'email@email.com',
            'input_min_length' => 6,
            'input_max_length' => 50,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => true,
            'sort_order'       => 1,
        ]);
        $this->createProducts($spotify->id, [
            ['code' => 'SPT-1M',  'name' => 'Spotify Premium 1 Bulan',  'base' => 54990, 'sell' => 57000, 'fee' => 1000, 'validity' => '30 Hari'],
            ['code' => 'SPT-3M',  'name' => 'Spotify Premium 3 Bulan',  'base' => 164970,'sell' => 169000,'fee' => 1000, 'validity' => '90 Hari'],
            ['code' => 'SPT-6M',  'name' => 'Spotify Premium 6 Bulan',  'base' => 329940,'sell' => 336000,'fee' => 1000, 'validity' => '180 Hari'],
            ['code' => 'SPT-12M', 'name' => 'Spotify Premium 12 Bulan', 'base' => 659880,'sell' => 670000,'fee' => 1000, 'validity' => '365 Hari'],
        ]);

        $youtube = Merchant::updateOrCreate(['code' => 'YT_PREMIUM'], [
            'merchant_category_id' => $hiburan->id,
            'name'             => 'YouTube Premium',
            'input_type'       => 'account_number',
            'input_label'      => 'Email Akun Google',
            'input_hint'       => 'email@gmail.com',
            'input_min_length' => 6,
            'input_max_length' => 50,
            'has_inquiry'      => false,
            'is_active'        => true,
            'is_featured'      => false,
            'sort_order'       => 2,
        ]);
        $this->createProducts($youtube->id, [
            ['code' => 'YTP-1M', 'name' => 'YouTube Premium 1 Bulan', 'base' => 59000, 'sell' => 62000, 'fee' => 1000, 'validity' => '30 Hari'],
            ['code' => 'YTP-3M', 'name' => 'YouTube Premium 3 Bulan', 'base' => 177000,'sell' => 183000,'fee' => 1000, 'validity' => '90 Hari'],
        ]);

        $this->command->info('MerchantSeeder selesai — ' .
            MerchantCategory::count() . ' kategori, ' .
            Merchant::count() . ' merchant, ' .
            MerchantProduct::count() . ' produk.');
    }

    // ── Helper ────────────────────────────────────────────────────────────────
    private function createProducts(int $merchantId, array $products): void
    {
        foreach ($products as $i => $p) {
            MerchantProduct::updateOrCreate(
                ['merchant_id' => $merchantId, 'code' => $p['code']],
                [
                    'name'         => $p['name'],
                    'base_price'   => $p['base'],
                    'selling_price'=> $p['sell'],
                    'admin_fee'    => $p['fee'],
                    'category_tag' => $p['tag']      ?? null,
                    'validity'     => $p['validity'] ?? null,
                    'is_available' => true,
                    'sort_order'   => $i + 1,
                ]
            );
        }
    }
}