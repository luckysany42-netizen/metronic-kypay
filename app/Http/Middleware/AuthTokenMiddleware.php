<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthTokenMiddleware
{
    /**
     * Cek Authorization: Token {api_token} di header request.
     * Sesuai dengan sistem auth yang sudah ada di AuthController.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Token ')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token tidak ditemukan.',
            ], 401);
        }

        $token = str_replace('Token ', '', $header);
        $user  = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token tidak valid atau sudah kadaluarsa.',
            ], 401);
        }

        // Inject user ke dalam request agar bisa diakses via $request->user()
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}