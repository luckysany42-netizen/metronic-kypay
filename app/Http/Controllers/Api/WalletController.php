<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $user   = $request->user();
        $wallet = $user->wallet()->with('user:id,name,email,avatar')->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet belum dibuat. Silakan hubungi admin.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'wallet_number'        => $wallet->wallet_number,
                'wallet_name'          => $wallet->wallet_name,
                'balance'              => (float) $wallet->balance,
                'locked_balance'       => (float) $wallet->locked_balance,
                'available_balance'    => (float) ($wallet->balance - $wallet->locked_balance),
                'status'               => $wallet->status,
                'pin_set'              => (bool) $wallet->pin_set,
                'daily_transfer_limit' => (float) $wallet->daily_transfer_limit,
                'daily_topup_limit'    => (float) $wallet->daily_topup_limit,
                'last_transaction_at'  => $wallet->last_transaction_at,
            ],
        ]);
    }

    public function transactions(Request $request)
    {
        $user   = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet tidak ditemukan.'], 404);
        }

        $transactions = $wallet->transactions()
            ->with('relatedWallet.user:id,name,avatar')
            ->latest()
            ->paginate(15);

        $mapped = $transactions->getCollection()->map(function ($trx) {
            return [
                'id'                 => $trx->id,
                'transaction_number' => $trx->transaction_number,
                'type'               => $trx->type,
                'type_label'         => $trx->type_label ?? $trx->type,
                'amount'             => (float) $trx->amount,
                'fee'                => (float) $trx->fee,
                'balance_before'     => (float) $trx->balance_before,
                'balance_after'      => (float) $trx->balance_after,
                'status'             => $trx->status,
                'description'        => $trx->description,
                'note'               => $trx->note,
                'is_credit'          => $trx->isCredit(),
                'related_user'       => $trx->relatedWallet?->user ? [
                    'name'   => $trx->relatedWallet->user->name,
                    'avatar' => $trx->relatedWallet->user->avatar,
                ] : null,
                'created_at' => $trx->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $mapped,
            'meta'    => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }

    /**
     * POST /api/wallet/set-pin
     * FIX: cek pin_set dari DB bukan dari request body
     */
    public function setPin(Request $request)
    {
        $user   = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet tidak ditemukan.'], 404);
        }

        // Validasi dinamis — current_pin hanya wajib jika PIN sudah ada di DB
        $rules = [
            'pin'              => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'pin_confirmation' => 'required|same:pin',
        ];

        if ($wallet->pin_set) {
            $rules['current_pin'] = 'required|string|size:6';
        }

        $request->validate($rules);

        // Verifikasi PIN lama
        if ($wallet->pin_set) {
            if (!$wallet->verifyPin($request->current_pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN saat ini tidak valid.',
                ], 422);
            }
        }

        $isNew = !$wallet->pin_set;

        $wallet->update([
            'pin'     => Hash::make($request->pin),
            'pin_set' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => $isNew ? 'PIN berhasil dibuat.' : 'PIN berhasil diperbarui.',
        ]);
    }

    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        $wallet = $request->user()->wallet;

        if (!$wallet || !$wallet->pin_set) {
            return response()->json([
                'success' => false,
                'message' => 'PIN belum diset. Silakan buat PIN terlebih dahulu.',
            ], 422);
        }

        $valid = $wallet->verifyPin($request->pin);

        return response()->json([
            'success' => $valid,
            'message' => $valid ? 'PIN valid.' : 'PIN tidak valid.',
        ], $valid ? 200 : 422);
    }

    public function findByNumber(Request $request, string $walletNumber)
    {
        $myWallet = $request->user()->wallet;

        if ($myWallet && $myWallet->wallet_number === $walletNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa transfer ke wallet sendiri.',
            ], 422);
        }

        $wallet = Wallet::where('wallet_number', $walletNumber)
            ->where('status', 'active')
            ->with('user:id,name,avatar')
            ->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor wallet tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'wallet_number' => $wallet->wallet_number,
                'wallet_name'   => $wallet->wallet_name,
                'owner_name'    => $wallet->user->name,
                'owner_avatar'  => $wallet->user->avatar,
            ],
        ]);
    }
}