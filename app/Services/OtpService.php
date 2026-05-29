<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Str;

class OtpService
{
    public function __construct(
        private readonly FonnteService $fonnte
    ) {}

    // -------------------------------------------------------------------------
    // Public Methods
    // -------------------------------------------------------------------------

    /**
     * Generate OTP baru dan kirim via SMS ke nomor HP.
     * Dipanggil dari OtpController saat user minta kirim OTP.
     *
     * @param  string $phone    Nomor HP tujuan
     * @param  string $purpose  Tujuan OTP: register | reset_pin | change_phone
     * @param  string|null $ip  IP address request untuk rate limiting
     * @return array
     */
    public function sendOtp(string $phone, string $purpose, ?string $ip = null): array
    {
        $phone = User::normalizePhone($phone);

        // Cek cooldown — cegah spam request OTP
        if (OtpVerification::isInCooldown($phone, $purpose)) {
            $remaining = OtpVerification::cooldownRemainingSeconds($phone, $purpose);

            return [
                'success'            => false,
                'message'            => "Tunggu {$remaining} detik sebelum meminta OTP baru.",
                'cooldown_remaining' => $remaining,
            ];
        }

        // Invalidasi semua OTP lama untuk nomor + purpose ini
        OtpVerification::invalidateOldOtps($phone, $purpose);

        // Generate kode OTP 6 digit
        $code = $this->generateCode();

        // Simpan OTP ke database
        OtpVerification::create([
            'phone'      => $phone,
            'code'       => $code,
            'purpose'    => $purpose,
            'expired_at' => now()->addMinutes(OtpVerification::EXPIRY_MINUTES),
            'ip_address' => $ip,
        ]);

        // Kirim SMS via Fonnte
        $result = $this->fonnte->sendOtp($phone, $code);

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => $result['message'],
            ];
        }

        return [
            'success'            => true,
            'message'            => 'Kode OTP telah dikirim ke nomor HP kamu.',
            'expires_in_seconds' => OtpVerification::EXPIRY_MINUTES * 60,
            'cooldown_seconds'   => OtpVerification::RESEND_COOLDOWN_SECONDS,
            // Hanya tampilkan nomor HP yang di-mask untuk konfirmasi UI
            'phone_masked'       => $this->maskPhone($phone),
        ];
    }

    /**
     * Verifikasi kode OTP yang diinput pengguna.
     * Dipanggil dari OtpController saat user submit kode OTP.
     *
     * @param  string $phone    Nomor HP
     * @param  string $code     Kode OTP dari input pengguna
     * @param  string $purpose  Tujuan OTP
     * @return array
     */
    public function verifyOtp(string $phone, string $code, string $purpose): array
    {
        $phone = User::normalizePhone($phone);

        // Cari OTP yang valid untuk nomor + purpose ini
        $otp = OtpVerification::valid()
            ->forPhone($phone)
            ->forPurpose($purpose)
            ->latest()
            ->first();

        // OTP tidak ditemukan / sudah expired / sudah dipakai / sudah dikunci
        if (! $otp) {
            return [
                'success' => false,
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa. Minta kode baru.',
            ];
        }

        // Kode salah — tambah attempt count
        if ($otp->code !== $code) {
            $otp->incrementAttempt();

            $remaining = $otp->fresh()->remaining_attempts;

            if ($remaining <= 0) {
                return [
                    'success'            => false,
                    'message'            => 'Kode OTP terkunci karena terlalu banyak percobaan salah. Minta kode baru.',
                    'remaining_attempts' => 0,
                ];
            }

            return [
                'success'            => false,
                'message'            => "Kode OTP salah. Sisa percobaan: {$remaining}x.",
                'remaining_attempts' => $remaining,
            ];
        }

        // Kode benar — tandai sebagai digunakan
        $otp->markAsUsed();

        // Jika purpose register: tandai nomor HP user sebagai terverifikasi
        if ($purpose === OtpVerification::PURPOSE_REGISTER) {
            $user = User::where('phone', $phone)
                ->orWhere('phone', '0' . substr($phone, 2)) // Handle format 08xx
                ->first();

            if ($user) {
                $user->markPhoneAsVerified();
            }
        }

        return [
            'success' => true,
            'message' => 'Nomor HP berhasil diverifikasi.',
            'phone'   => $phone,
            'purpose' => $purpose,
        ];
    }

    /**
     * Cek status OTP terakhir untuk nomor HP tertentu.
     * Digunakan Flutter untuk restore state jika app ditutup saat OTP aktif.
     *
     * @param  string $phone
     * @param  string $purpose
     * @return array
     */
    public function getOtpStatus(string $phone, string $purpose): array
    {
        $phone = User::normalizePhone($phone);

        $otp = OtpVerification::valid()
            ->forPhone($phone)
            ->forPurpose($purpose)
            ->latest()
            ->first();

        if (! $otp) {
            return [
                'has_active_otp'     => false,
                'cooldown_remaining' => OtpVerification::cooldownRemainingSeconds($phone, $purpose),
            ];
        }

        return [
            'has_active_otp'     => true,
            'remaining_seconds'  => $otp->remaining_seconds,
            'remaining_attempts' => $otp->remaining_attempts,
            'cooldown_remaining' => 0,
            'phone_masked'       => $this->maskPhone($phone),
        ];
    }

    // -------------------------------------------------------------------------
    // Private Methods
    // -------------------------------------------------------------------------

    /**
     * Generate kode OTP 6 digit angka.
     * Tidak dimulai dari 0 untuk menghindari masalah tampilan di Flutter.
     */
    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * Sembunyikan sebagian nomor HP untuk konfirmasi di UI Flutter.
     * Contoh: 081234567890 → 0812****7890
     */
    private function maskPhone(string $phone): string
    {
        // Konversi ke format 08xx untuk tampilan Indonesia
        if (str_starts_with($phone, '62')) {
            $phone = '0' . substr($phone, 2);
        }

        if (strlen($phone) < 8) return '****';

        return substr($phone, 0, 4)
            . '****'
            . substr($phone, -4);
    }
}