<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneVerified
{
    /**
     * Pastikan user sudah memverifikasi nomor HP via OTP
     * sebelum bisa mengakses endpoint tertentu.
     *
     * Penggunaan di api.php:
     *   Route::middleware(['auth.token', 'phone.verified'])->group(...)
     *
     * Daftarkan di bootstrap/app.php (Laravel 12):
     *   ->withMiddleware(function (Middleware $middleware) {
     *       $middleware->alias([
     *           'phone.verified' => \App\Http\Middleware\EnsurePhoneVerified::class,
     *       ]);
     *   })
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user()
            ?? $request->attributes->get('auth_user'); // Sesuaikan dengan AuthTokenMiddleware yang sudah ada

        // Jika tidak ada user (harusnya sudah ditangani auth.token middleware)
        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        // Cek apakah HP sudah diverifikasi
        if (! $user->is_phone_verified) {
            return response()->json([
                'status'  => false,
                'message' => 'Nomor HP belum diverifikasi. Silakan verifikasi nomor HP kamu terlebih dahulu.',
                'data'    => [
                    'phone'              => $user->phone,
                    'requires_otp'       => true,
                ],
            ], 403);
        }

        return $next($request);
    }
}