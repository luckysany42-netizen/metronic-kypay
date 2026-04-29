<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FixAdminPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update admin user dengan password yang di-hash dengan benar
        $admin = User::where('email', 'adminsigma@admin.com')->first();
        
        if ($admin) {
            // Hash password dengan benar
            $admin->password = Hash::make('123456789');
            $admin->save();
            
            $this->command->info('✅ Admin password updated: adminsigma@admin.com / 123456789');
        } else {
            // Jika tidak ada, buat admin baru
            User::create([
                'name'      => 'Admin',
                'email'     => 'adminsigma@admin.com',
                'password'  => Hash::make('123456789'),
                'api_token' => \Illuminate\Support\Str::random(60),
                'role'      => 'admin',
                'phone'     => '081234567890',
            ]);
            
            $this->command->info('✅ Admin user created: adminsigma@admin.com / 123456789');
        }
    }
}
