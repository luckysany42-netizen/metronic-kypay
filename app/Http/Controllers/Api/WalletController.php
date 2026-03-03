<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    /**
     * GET /api/wallet
     */
    public function show(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['errors' => ['auth' => 'Unauthorized']], 401);
        }

        $wallet = $user->wallet;
        if (!$wallet) {
            return response()->json(['errors' => ['wallet' => 'Wallet tidak ditemukan']], 404);
        }

        return response()->json([
            'wallet' => $wallet,
            'balance' => number_format($wallet->balance, 0, ',', '.'),
            'available_balance' => number_format($wallet->available_balance, 0, ',', '.'),
        ]);
    }

    /**
     * POST /api/wallet/set-initial-pin
     */
    public function setInitialPin(Request $request)
    {
        $request->validate([
            'api_token'        => 'required|string',
            'pin'              => 'required|digits:6',
            'pin_confirmation' => 'required|digits:6|same:pin',
        ]);

        $user = User::where('api_token', $request->api_token)->first();
        if (!$user) {
            return response()->json(['errors' => ['api_token' => 'Token tidak valid atau sudah kadaluarsa']], 401);
        }

        $wallet = $user->wallet;
        if (!$wallet) {
            return response()->json(['errors' => ['wallet' => 'Wallet tidak ditemukan']], 404);
        }

        if ($wallet->pin_set) {
            return response()->json(['errors' => ['pin' => 'PIN sudah pernah dibuat sebelumnya']], 422);
        }

        if (preg_match('/^(.)\1{5}$/', $request->pin)) {
            return response()->json(['errors' => ['pin' => 'PIN tidak boleh 6 angka yang sama']], 422);
        }
        if (in_array($request->pin, ['123456', '654321'])) {
            return response()->json(['errors' => ['pin' => 'PIN terlalu mudah ditebak']], 422);
        }

        $wallet->pin     = Hash::make($request->pin);
        $wallet->pin_set = true;
        $wallet->save();

        return response()->json(['message' => 'PIN berhasil dibuat! Silakan masuk ke akun kamu.']);
    }

    /**
     * POST /api/wallet/set-pin
     */
    public function setPin(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['errors' => ['auth' => 'Unauthorized']], 401);
        }

        $wallet = $user->wallet;
        if (!$wallet) {
            return response()->json(['errors' => ['wallet' => 'Wallet tidak ditemukan']], 404);
        }

        $rules = [
            'pin'              => 'required|digits:6',
            'pin_confirmation' => 'required|digits:6|same:pin',
        ];

        if ($wallet->pin_set) {
            $rules['current_pin'] = 'required|digits:6';
        }

        $request->validate($rules);

        if ($wallet->pin_set) {
            if (!Hash::check($request->current_pin, $wallet->pin)) {
                return response()->json(['errors' => ['current_pin' => 'PIN lama tidak sesuai']], 422);
            }
        }

        $wallet->pin     = Hash::make($request->pin);
        $wallet->pin_set = true;
        $wallet->save();

        return response()->json(['message' => $wallet->pin_set ? 'PIN berhasil diubah!' : 'PIN berhasil dibuat!']);
    }

    /**
     * GET /api/wallet/find/{wallet_number}
     * Cari wallet berdasarkan nomor wallet persis (untuk transfer)
     */
    public function findByNumber(Request $request, string $walletNumber)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['errors' => ['auth' => 'Unauthorized']], 401);
        }

        $wallet = Wallet::with('user')
            ->where('wallet_number', $walletNumber)
            ->where('status', 'active')
            ->first();

        if (!$wallet) {
            return response()->json(['errors' => ['wallet' => 'Nomor wallet tidak ditemukan']], 404);
        }

        if ($wallet->user_id === $user->id) {
            return response()->json(['errors' => ['wallet' => 'Tidak bisa transfer ke wallet sendiri']], 422);
        }

        return response()->json([
            'wallet' => [
                'id'            => $wallet->id,
                'wallet_number' => $wallet->wallet_number,
                'wallet_name'   => $wallet->wallet_name,
                'user_name'     => $wallet->user->name ?? '-',
                'avatar'        => $wallet->user->avatar ?? null,
            ]
        ]);
    }

    /**
     * GET /api/wallet/search?q=nomor_atau_nama
     */
    public function search(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['errors' => ['auth' => 'Unauthorized']], 401);
        }

        $q = $request->query('q', '');
        if (strlen($q) < 3) {
            return response()->json(['wallets' => []]);
        }

        $wallets = Wallet::with('user')
            ->where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->where(function ($query) use ($q) {
                $query->where('wallet_number', 'like', "%$q%")
                      ->orWhere('wallet_name', 'like', "%$q%")
                      ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%$q%"));
            })
            ->limit(10)
            ->get()
            ->map(fn($w) => [
                'id'            => $w->id,
                'wallet_number' => $w->wallet_number,
                'wallet_name'   => $w->wallet_name,
                'user_name'     => $w->user->name ?? '-',
                'avatar'        => $w->user->avatar ?? null,
            ]);

        return response()->json(['wallets' => $wallets]);
    }

    /**
     * GET /api/wallet/transactions
     */
    public function transactions(Request $request)
    {
        $user = $this->getUserFromToken($request);
        if (!$user) {
            return response()->json(['errors' => ['auth' => 'Unauthorized']], 401);
        }

        $wallet = $user->wallet;
        if (!$wallet) {
            return response()->json(['errors' => ['wallet' => 'Wallet tidak ditemukan']], 404);
        }

        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($transactions);
    }

    private function getUserFromToken(Request $request): ?User
    {
        $header = $request->header('Authorization');
        if (!$header) return null;
        $token = str_replace('Token ', '', $header);
        return User::where('api_token', $token)->first();
    }
}