<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;

class UpdateMerchantLogosSeeder extends Seeder
{
    /**
     * Mapping merchant code dengan logo filename
     * Logo filename harus ada di public/uploads/kypay/merchant-logos/
     */
    private array $logoMappings = [
        // GAME
        'ML'         => 'mobile-legends.png',
        'FF'         => 'free-fire.png',
        'PUBG'       => 'pubg-mobile.png',
        
        // PULSA & DATA
        'TELKOMSEL'  => 'telkomsel.png',
        'INDOSAT'    => 'indosat.png',
        'XL'         => 'xl-axiata.png',
        
        // TAGIHAN
        'PLN_POST'   => 'pln-pascabayar.png',
        'BPJS'       => 'bpjs-kesehatan.png',
        'PDAM'       => 'pdam.png',
        
        // RUMAH TANGGA
        'PLN_PRE'    => 'pln-prabayar.png',
        
        // HIBURAN
        'SPOTIFY'    => 'spotify.png',
        'YT_PREMIUM' => 'youtube-premium.png',
    ];

    public function run(): void
    {
        foreach ($this->logoMappings as $code => $filename) {
            $merchant = Merchant::where('code', $code)->first();
            
            if ($merchant) {
                $merchant->update(['logo_url' => $filename]);
                echo "✓ Updated {$merchant->name} with logo: {$filename}\n";
            } else {
                echo "✗ Merchant with code '{$code}' not found\n";
            }
        }
    }
}
