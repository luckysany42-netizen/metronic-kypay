<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OtpController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService
    ) {}

    // -------------------------------------------------------------------------
    // Endpoints
    // -------------------------------------------------------------------------

    /**
     * POST /otp/send
     *
     * Kirim OTP ke nomor HP. Dipanggil Flutter setelah user input nomor HP
     * saat register. Juga dipakai untuk resend OTP.
     *
     * Body: { "phone": "08xx", "purpose": "register" }
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/',
            ],
            'purpose' => [
                'required',
                'string',
                Rule::in(['register', 'reset_pin', 'change_phone']),
            ],
        ], [
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex'    => 'Format nomor HP tidak valid. Gunakan format 08xx.',
            'purpose.in'     => 'Tujuan OTP tidak valid.',
        ]);

        $result = $this->otpService->sendOtp(
            phone:   $validated['phone'],
            purpose: $validated['purpose'],
            ip:      $request->ip(),
        );

        if (! $result['success']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
                'data'    => [
                    'cooldown_remaining' => $result['cooldown_remaining'] ?? 0,
                ],
            ], 429); // 429 Too Many Requests untuk cooldown
        }

        return response()->json([
            'status'  => true,
            'message' => $result['message'],
            'data'    => [
                'phone_masked'       => $result['phone_masked'],
                'expires_in_seconds' => $result['expires_in_seconds'],
                'cooldown_seconds'   => $result['cooldown_seconds'],
            ],
        ]);
    }

    /**
     * POST /otp/verify
     *
     * Verifikasi kode OTP yang diinput pengguna.
     * Jika berhasil, Flutter lanjut ke layar Buat PIN.
     *
     * Body: { "phone": "08xx", "code": "123456", "purpose": "register" }
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/',
            ],
            'code' => [
                'required',
                'string',
                'digits:6',
            ],
            'purpose' => [
                'required',
                'string',
                Rule::in(['register', 'reset_pin', 'change_phone']),
            ],
        ], [
            'phone.required'   => 'Nomor HP wajib diisi.',
            'phone.regex'      => 'Format nomor HP tidak valid.',
            'code.required'    => 'Kode OTP wajib diisi.',
            'code.digits'      => 'Kode OTP harus 6 digit angka.',
            'purpose.required' => 'Tujuan OTP wajib diisi.',
            'purpose.in'       => 'Tujuan OTP tidak valid.',
        ]);

        $result = $this->otpService->verifyOtp(
            phone:   $validated['phone'],
            code:    $validated['code'],
            purpose: $validated['purpose'],
        );

        if (! $result['success']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
                'data'    => [
                    'remaining_attempts' => $result['remaining_attempts'] ?? null,
                ],
            ], 422);
        }

        // Generate token sementara setelah OTP verified
        $user = \App\Models\User::where('phone', $validated['phone'])
            ->orWhere('phone', '0' . substr(preg_replace('/\D/', '', $validated['phone']), 2))
            ->first();

        $tempToken = null;
        if ($user && $validated['purpose'] === 'register') {
            $tempToken = \Illuminate\Support\Str::random(60);
            $user->update(['api_token' => $tempToken]);
        }

        return response()->json([
            'status'  => true,
            'message' => $result['message'],
            'data'    => [
                'phone'          => $result['phone'],
                'purpose'        => $result['purpose'],
                'phone_verified' => true,
                'api_token'      => $tempToken,
            ],
        ]);
    }

    /**
     * GET /otp/status?phone=08xx&purpose=register
     *
     * Cek status OTP aktif untuk nomor HP tertentu.
     * Digunakan Flutter untuk restore state countdown jika app ditutup.
     */
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/',
            ],
            'purpose' => [
                'required',
                'string',
                Rule::in(['register', 'reset_pin', 'change_phone']),
            ],
        ]);

        $result = $this->otpService->getOtpStatus(
            phone:   $validated['phone'],
            purpose: $validated['purpose'],
        );

        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => $result,
        ]);
    }
}