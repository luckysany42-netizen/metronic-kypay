<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['errors' => ['login' => 'Email atau password salah']], 401);
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
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:8|confirmed',
        ]);
        $user = User::create([
            'name'      => $request->first_name . ' ' . $request->last_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'api_token' => Str::random(60),
            'role'      => 'user',
        ]);
        return response()->json($user, 201);
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

    // ==========================================
    // AUTH - CHANGE PASSWORD (user sedang login)
    // ==========================================
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
            return response()->json([
                'errors' => ['current_password' => 'Password lama tidak sesuai']
            ], 422);
        }
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => ['password' => 'Password baru tidak boleh sama dengan password lama']
            ], 422);
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
        return response()->json($user);
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
        $request->validate(['avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();
        return response()->json([
            'message'    => 'Avatar berhasil diupload',
            'avatar'     => $user->avatar,
            'avatar_url' => asset('storage/' . $user->avatar),
            'user'       => $user,
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