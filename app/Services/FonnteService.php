<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    private string $apiKey;
    private string $baseUrl;
    private string $sender;

    public function __construct()
    {
        $this->apiKey  = config('services.fonnte.api_key');
        $this->baseUrl = config('services.fonnte.base_url', 'https://api.fonnte.com');
        $this->sender  = config('services.fonnte.sender');
    }

    // -------------------------------------------------------------------------
    // Public Methods
    // -------------------------------------------------------------------------

    /**
     * Kirim OTP via SMS ke nomor HP tujuan.
     *
     * @param  string $phone  Nomor HP format 08xx atau +628xx
     * @param  string $code   Kode OTP 6 digit
     * @return array          ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $phone, string $code): array
    {
        $phone   = $this->normalizePhone($phone);
        $message = $this->buildOtpMessage($code);

        return $this->send($phone, $message);
    }

    // -------------------------------------------------------------------------
    // Private Methods
    // -------------------------------------------------------------------------

    /**
     * Kirim pesan ke Fonnte API.
     */
    private function send(string $phone, string $message): array
    {
        try {
            Log::info('FonnteService: Preparing to send', [
                'phone'   => $this->maskPhone($phone),
                'sender'  => $this->sender,
                'type'    => 'whatsapp',
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->asForm()
                ->post("{$this->baseUrl}/send", [
                    'target'  => $phone,
                    'message' => $message,
                    'sender'  => $this->sender,
                    'type'    => 'whatsapp',        // Kirim via WhatsApp
                    'delay'   => '1',               // Delay pengiriman dalam detik
                ]);

            $body = $response->json();

            Log::info('FonnteService: API Response', [
                'phone'              => $this->maskPhone($phone),
                'http_status'        => $response->status(),
                'response_body'      => $body,
                'response_status'    => $body['status'] ?? 'N/A',
            ]);

            // Fonnte mengembalikan status true/false di field 'status'
            if ($response->successful() && ($body['status'] ?? false) === true) {
                Log::info('FonnteService: OTP sent successfully', [
                    'phone'  => $this->maskPhone($phone),
                    'target' => $body['target'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message' => 'OTP berhasil dikirim',
                ];
            }

            // Fonnte return response tapi status false
            $reason = $body['reason'] ?? $body['message'] ?? 'Gagal mengirim OTP';

            Log::warning('FonnteService: Failed to send OTP', [
                'phone'  => $this->maskPhone($phone),
                'reason' => $reason,
                'body'   => $body,
            ]);

            return [
                'success' => false,
                'message' => $reason,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('FonnteService: Connection error', [
                'phone' => $this->maskPhone($phone),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke layanan SMS. Coba lagi.',
            ];

        } catch (\Exception $e) {
            Log::error('FonnteService: Unexpected error', [
                'phone' => $this->maskPhone($phone),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan. Coba lagi.',
            ];
        }
    }

    /**
     * Template pesan OTP yang dikirim ke pengguna.
     */
    private function buildOtpMessage(string $code): string
    {
        return "Kode OTP KyPay kamu adalah: *{$code}*\n\n"
            . "Berlaku selama 5 menit.\n"
            . "Jangan bagikan kode ini kepada siapapun, termasuk pihak KyPay.\n\n"
            . "Abaikan pesan ini jika kamu tidak merasa mendaftar.";
    }

    /**
     * Normalisasi nomor HP ke format internasional (628xx) yang diterima Fonnte.
     * Fonnte SMS membutuhkan format tanpa '+', contoh: 6281234567890
     */
    private function normalizePhone(string $phone): string
    {
        // Hapus semua karakter selain angka
        $phone = preg_replace('/\D/', '', $phone);

        // Ganti awalan 0 → 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Hapus '+' jika ada (sudah dihandle di atas, tapi jaga-jaga)
        $phone = ltrim($phone, '+');

        return $phone;
    }

    /**
     * Sembunyikan sebagian nomor HP untuk keperluan logging.
     * Contoh: 6281234567890 → 6281****7890
     */
    private function maskPhone(string $phone): string
    {
        if (strlen($phone) < 8) return '****';

        return substr($phone, 0, 4)
            . '****'
            . substr($phone, -4);
    }
}