<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'code',
        'purpose',
        'is_used',
        'expired_at',
        'verified_at',
        'attempt_count',
        'ip_address',
    ];

    protected $casts = [
        'is_used'       => 'boolean',
        'expired_at'    => 'datetime',
        'verified_at'   => 'datetime',
        'attempt_count' => 'integer',
    ];

    // Konstanta purpose — gunakan ini di seluruh codebase
    const PURPOSE_REGISTER     = 'register';
    const PURPOSE_RESET_PIN    = 'reset_pin';
    const PURPOSE_CHANGE_PHONE = 'change_phone';

    // Batas percobaan salah sebelum OTP dikunci
    const MAX_ATTEMPTS = 5;

    // Durasi berlaku OTP dalam menit
    const EXPIRY_MINUTES = 5;

    // Cooldown resend OTP dalam detik
    const RESEND_COOLDOWN_SECONDS = 60;

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * OTP yang masih valid: belum dipakai, belum expired, belum melebihi max attempt.
     *
     * Penggunaan: OtpVerification::valid()->where('phone', $phone)->first()
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query
            ->where('is_used', false)
            ->where('expired_at', '>', now())
            ->where('attempt_count', '<', self::MAX_ATTEMPTS);
    }

    /**
     * Filter berdasarkan nomor HP.
     *
     * Penggunaan: OtpVerification::forPhone('08xx')->get()
     */
    public function scopeForPhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone', $phone);
    }

    /**
     * Filter berdasarkan purpose.
     *
     * Penggunaan: OtpVerification::forPurpose('register')->get()
     */
    public function scopeForPurpose(Builder $query, string $purpose): Builder
    {
        return $query->where('purpose', $purpose);
    }

    /**
     * OTP yang sudah expired.
     *
     * Penggunaan untuk cleanup: OtpVerification::expired()->delete()
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expired_at', '<=', now());
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Apakah OTP ini sudah expired.
     * Accessor: $otp->is_expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expired_at->isPast();
    }

    /**
     * Apakah OTP ini masih bisa dipakai.
     * Accessor: $otp->is_valid
     */
    public function getIsValidAttribute(): bool
    {
        return ! $this->is_used
            && ! $this->is_expired
            && $this->attempt_count < self::MAX_ATTEMPTS;
    }

    /**
     * Sisa waktu OTP dalam detik (untuk countdown di Flutter).
     * Accessor: $otp->remaining_seconds
     */
    public function getRemainingSecondsAttribute(): int
    {
        if ($this->is_expired) return 0;
        return (int) now()->diffInSeconds($this->expired_at);
    }

    /**
     * Sisa percobaan yang diizinkan.
     * Accessor: $otp->remaining_attempts
     */
    public function getRemainingAttemptsAttribute(): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempt_count);
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    /**
     * Tandai OTP sebagai sudah digunakan dan catat waktu verifikasi.
     *
     * Penggunaan di OtpService:
     *   $otp->markAsUsed();
     */
    public function markAsUsed(): bool
    {
        return $this->update([
            'is_used'     => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Tambah hitungan percobaan salah.
     * Otomatis lock jika sudah mencapai MAX_ATTEMPTS.
     *
     * Penggunaan di OtpService:
     *   $otp->incrementAttempt();
     */
    public function incrementAttempt(): bool
    {
        $this->increment('attempt_count');

        // Jika sudah melebihi batas, langsung expired-kan OTP ini
        if ($this->attempt_count >= self::MAX_ATTEMPTS) {
            return $this->update(['expired_at' => now()]);
        }

        return true;
    }

    /**
     * Cek apakah nomor HP ini masih dalam cooldown resend.
     * Mencegah spam request OTP baru.
     *
     * Penggunaan di OtpService:
     *   OtpVerification::isInCooldown($phone, 'register')
     */
    public static function isInCooldown(string $phone, string $purpose): bool
    {
        $lastOtp = static::forPhone($phone)
            ->forPurpose($purpose)
            ->latest()
            ->first();

        if (! $lastOtp) return false;

        $cooldownEnd = $lastOtp->created_at->addSeconds(self::RESEND_COOLDOWN_SECONDS);

        return now()->lt($cooldownEnd);
    }

    /**
     * Sisa detik cooldown resend untuk nomor HP ini.
     * Dikembalikan ke Flutter untuk tampilan tombol "Kirim Ulang (60s)".
     *
     * Penggunaan di OtpService:
     *   OtpVerification::cooldownRemainingSeconds($phone, 'register')
     */
    public static function cooldownRemainingSeconds(string $phone, string $purpose): int
    {
        $lastOtp = static::forPhone($phone)
            ->forPurpose($purpose)
            ->latest()
            ->first();

        if (! $lastOtp) return 0;

        $cooldownEnd = $lastOtp->created_at->addSeconds(self::RESEND_COOLDOWN_SECONDS);

        if (now()->gte($cooldownEnd)) return 0;

        return (int) now()->diffInSeconds($cooldownEnd);
    }

    /**
     * Invalidasi semua OTP lama untuk nomor HP & purpose yang sama.
     * Dipanggil sebelum membuat OTP baru agar tidak ada OTP duplikat aktif.
     *
     * Penggunaan di OtpService sebelum generate OTP baru:
     *   OtpVerification::invalidateOldOtps($phone, 'register');
     */
    public static function invalidateOldOtps(string $phone, string $purpose): void
    {
        static::forPhone($phone)
            ->forPurpose($purpose)
            ->where('is_used', false)
            ->update([
                'is_used'    => true,
                'expired_at' => now(),
            ]);
    }
}