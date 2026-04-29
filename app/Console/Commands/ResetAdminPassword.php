<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:reset-password {email=adminsigma@admin.com} {password=123456789}';

    /**
     * The console command description.
     */
    protected $description = 'Reset admin password with proper hashing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $admin = User::where('email', $email)->first();
        
        if ($admin) {
            $admin->password = Hash::make($password);
            $admin->api_token = Str::random(60);
            $admin->save();
            
            $this->info("✅ Admin password reset successfully!");
            $this->info("📧 Email: {$email}");
            $this->info("🔐 Password: {$password}");
            return 0;
        }
        
        $this->error("❌ Admin user not found!");
        return 1;
    }
}
