<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Cek apakah user yang login memiliki role yang sesuai.
     *
     * Cara pakai di routes:
     * Route::middleware(['auth:sanctum', 'role:admin'])->group(...)
     * Route::middleware(['auth:sanctum', 'role:admin,user'])->group(...) // multi-role
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Silakan login terlebih dahulu.',
            ], 401);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Kamu tidak memiliki akses ke resource ini.',
            ], 403);
        }

        return $next($request);
    }
}

// ================================================================
// CARA DAFTARKAN MIDDLEWARE INI:
//
// Di Laravel 12, daftarkan di bootstrap/app.php:
//
// ->withMiddleware(function (Middleware $middleware) {
//     $middleware->alias([
//         'role' => \App\Http\Middleware\RoleMiddleware::class,
//     ]);
// })
//
// ================================================================