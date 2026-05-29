<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required',
        ]);

        // Bersihkan semua karakter non-digit kecuali +
        $input = preg_replace('/[^0-9]/', '', $request->phone);

        // Normalisasi ke +62xxx
        if (str_starts_with($input, '62')) {
            $normalized = '+' . $input;
        } elseif (str_starts_with($input, '0')) {
            $normalized = '+62' . substr($input, 1);
        } else {
            $normalized = '+62' . $input;
        }

        // Cari user — coba berbagai format
        $user = User::where('phone', $normalized)
            ->orWhere('phone', $input)
            ->orWhere('phone', '0' . substr($input, 2)) // 08xx
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['login' => 'Nomor HP atau password salah']], 401);
        }

        $user->api_token = Str::random(60);
        $user->save();
        return response()->json($user);
    }

    public function adminLogin(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['login' => 'Email atau password salah']], 401);
        }
        if ($user->role !== 'admin') {
            return response()->json(['errors' => ['login' => 'Akun ini bukan admin']], 403);
        }
        $user->api_token = Str::random(60);
        $user->save();
        return response()->json($user);
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20|unique:users,phone',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'                => $request->first_name . ' ' . $request->last_name,
            'email'               => $request->email,
            'phone'               => User::normalizePhone($request->phone),
            'password'            => Hash::make($request->password),
            'role'                => 'user',
            'is_phone_verified'   => false,
        ]);

        Wallet::create([
            'user_id'              => $user->id,
            'wallet_number'        => Wallet::generateWalletNumber(),
            'wallet_name'          => $user->name . "'s KyPay",
            'balance'              => 0,
            'locked_balance'       => 0,
            'status'               => 'active',
            'pin_set'              => false,
            'daily_transfer_limit' => 5000000,
            'daily_topup_limit'    => 10000000,
        ]);

        // Kirim OTP otomatis setelah register
        app(\App\Services\OtpService::class)->sendOtp(
            phone:   $user->phone,
            purpose: 'register',
            ip:      $request->ip(),
        );

        return response()->json([
            'status'  => true,
            'message' => 'Registrasi berhasil. Kode OTP telah dikirim ke nomor HP kamu.',
            'data'    => [
                'phone'              => $user->phone,
                'phone_masked'       => substr($user->phone, 0, 4) . '****' . substr($user->phone, -4),
                'expires_in_seconds' => 300,
                'cooldown_seconds'   => 60,
            ],
        ], 201);
    }

    public function adminRegister(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'      => $request->first_name . ' ' . $request->last_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'api_token' => Str::random(60),
            'role'      => 'admin',
        ]);

        Wallet::create([
            'user_id'              => $user->id,
            'wallet_number'        => Wallet::generateWalletNumber(),
            'wallet_name'          => $user->name . "'s KyPay",
            'balance'              => 0,
            'locked_balance'       => 0,
            'status'               => 'active',
            'pin_set'              => false,
            'daily_transfer_limit' => 50000000,
            'daily_topup_limit'    => 100000000,
        ]);

        return response()->json($user, 201);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['errors' => ['email' => 'Email tidak ditemukan di sistem kami']], 404);
        }
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);
        $resetUrl = config('app.frontend_url', 'http://localhost:5173')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($request->email);
        \Mail::to($user->email)->send(new \App\Mail\ResetPasswordMail($resetUrl, $user->name));
        return response()->json(['message' => 'Link reset password telah dikirim ke email kamu']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);
        $tokenRecord = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!$tokenRecord) {
            return response()->json(['errors' => ['token' => 'Link reset password tidak valid atau sudah kadaluarsa']], 400);
        }
        if (!Hash::check($request->token, $tokenRecord->token)) {
            return response()->json(['errors' => ['token' => 'Link reset password tidak valid']], 400);
        }
        $createdAt = Carbon::parse($tokenRecord->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['errors' => ['token' => 'Link reset password sudah kadaluarsa, silakan minta ulang']], 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['errors' => ['email' => 'User tidak ditemukan']], 404);
        }
        $user->password  = Hash::make($request->password);
        $user->api_token = Str::random(60);
        $user->save();
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return response()->json(['message' => 'Password berhasil direset! Silakan masuk dengan password baru kamu.']);
    }

    public function changePassword(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['errors' => ['auth' => 'Unauthorized']], 401);
        }
        $request->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['errors' => ['current_password' => 'Password lama tidak sesuai']], 422);
        }
        if (Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['password' => 'Password baru tidak boleh sama dengan password lama']], 422);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['message' => 'Password berhasil diubah!']);
    }

    public function verifyToken(Request $request)
    {
        $request->validate(['api_token' => 'required']);
        $user = User::where('api_token', $request->api_token)->first();
        if (!$user) {
            return response()->json(['errors' => ['token' => 'Token tidak valid']], 401);
        }
        
        // Return user data with relative path
        $userData = $user->toArray();
        if ($user->avatar) {
            $userData['avatar_url'] = '/uploads/avatars/' . $user->avatar;
        } else {
            $userData['avatar_url'] = null;
        }
        
        return response()->json([
            'success' => true,
            'user' => $userData,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if ($user) {
            $user->api_token = null;
            $user->save();
        }
        return response()->json(['message' => 'Logout berhasil']);
    }

    public function updateProfile(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['errors' => ['auth' => 'Unauthorized']], 401);
        }
        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'phone'     => 'sometimes|nullable|string|max:20',
            'bio'       => 'sometimes|nullable|string|max:500',
            'job_title' => 'sometimes|nullable|string|max:100',
            'company'   => 'sometimes|nullable|string|max:100',
        ]);
        $user->update($request->only(['name', 'phone', 'bio', 'job_title', 'company']));
        return response()->json($user);
    }

    public function uploadAvatar(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['errors' => ['auth' => 'Unauthorized']], 401);
        }

        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpg,jpeg,png|max:5242880',
            ], [
                'avatar.required'   => 'File foto profil wajib diupload',
                'avatar.image'      => 'File harus berupa gambar yang valid',
                'avatar.mimes'      => 'Format file hanya support JPEG (.jpg, .jpeg) dan PNG (.png)',
                'avatar.max'        => 'Ukuran file maksimal 5MB',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        try {
            $file = $request->file('avatar');
            $filename = \uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Ensure directory exists
            $avatarDir = \public_path('uploads/avatars');
            if (!\file_exists($avatarDir)) {
                \mkdir($avatarDir, 0755, true);
            }
            
            // Save file as-is
            $file->move($avatarDir, $filename);
            
            // Delete old avatar
            if ($user->avatar) {
                $oldPath = \public_path('uploads/avatars/' . $user->avatar);
                if (\file_exists($oldPath)) {
                    @\unlink($oldPath);
                }
            }
            
            // Update database
            $user->avatar = $filename;
            $user->save();
            
            return response()->json([
                'success'    => true,
                'message'    => 'Foto profil berhasil diperbarui',
                'avatar'     => $filename,
                'avatar_url' => '/uploads/avatars/' . $filename,
                'user'       => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['avatar' => $e->getMessage()],
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token tidak valid atau sudah kadaluarsa.',
            ], 401);
        }

        $request->validate(['password' => 'required|string']);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password tidak sesuai.',
            ], 422);
        }

        $wallet = $user->wallet;
        if ($wallet && $wallet->balance > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo KyPay kamu masih Rp ' . number_format($wallet->balance, 0, ',', '.') . '. Kosongkan saldo terlebih dahulu.',
            ], 422);
        }

        DB::transaction(function () use ($user, $wallet) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            if ($wallet) {
                DB::table('transactions')->where('wallet_id', $wallet->id)->delete();
                $wallet->delete();
            }
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            // Hapus token dulu agar invalid sebelum user dihapus
            $user->api_token = null;
            $user->save();
            $user->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus.',
        ]);
    }

    private function getUserFromToken(Request $request): ?User
    {
        $header = $request->header('Authorization');
        if (!$header) return null;
        $token = str_replace('Token ', '', $header);
        return User::where('api_token', $token)->first();
    }
}