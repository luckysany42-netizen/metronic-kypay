<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'api_token',
        'role',
        'phone',
        'avatar',
        'bio',
        'job_title',
        'company',
        'is_phone_verified',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'favorite_contacts'   => 'array',
            'is_phone_verified'   => 'boolean',
            'phone_verified_at'   => 'datetime',
        ];
    }

    // KyPay: Relasi ke Wallet
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Cek apakah nomor HP user sudah diverifikasi OTP.
     * Penggunaan: $user->isPhoneVerified()
     */
    public function isPhoneVerified(): bool
    {
        return $this->is_phone_verified === true;
    }

    /**
     * Tandai nomor HP user sebagai sudah terverifikasi.
     * Dipanggil di OtpService setelah OTP berhasil diverifikasi.
     *
     * Penggunaan: $user->markPhoneAsVerified();
     */
    public function markPhoneAsVerified(): bool
    {
        return $this->update([
            'is_phone_verified' => true,
            'phone_verified_at' => now(),
        ]);
    }

    /**
     * Normalisasi nomor HP ke format 08xx (tanpa +62 atau 62 di depan).
     * Berguna saat menyimpan dan mencocokkan nomor HP dengan OTP.
     *
     * Penggunaan: User::normalizePhone('+6281234567890') → '081234567890'
     */
    public static function normalizePhone(string $phone): string
    {
        // Hapus semua karakter selain angka
        $phone = preg_replace('/\D/', '', $phone);

        // Ganti awalan 62 → 0
        if (str_starts_with($phone, '62')) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }
}