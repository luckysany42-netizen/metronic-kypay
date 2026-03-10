<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminUserController extends Controller
{
    /**
     * GET /api/admin/users
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user')
            ->with(['wallet']);

        // Filter status wallet
        if ($request->status === 'active') {
            $query->whereHas('wallet', fn($q) => $q->where('status', 'active'));
        } elseif ($request->status === 'suspended') {
            $query->whereHas('wallet', fn($q) => $q->where('status', 'suspended'));
        }

        // Search
        if ($request->search) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q
                ->where('name', 'like', $s)
                ->orWhere('email', 'like', $s)
                ->orWhere('phone', 'like', $s)
                ->orWhereHas('wallet', fn($wq) => $wq->where('wallet_number', 'like', $s))
            );
        }

        $paginated = $query->latest()->paginate(15);

        $data = $paginated->map(function ($user) {
            $wallet = $user->wallet;
            $walletId = $wallet?->id;

            // Hitung stats transaksi
            $totalTrx       = $walletId ? Transaction::where('wallet_id', $walletId)->count() : 0;
            $totalTopup     = $walletId ? Transaction::where('wallet_id', $walletId)->where('type', 'top_up')->where('status', 'success')->sum('amount') : 0;
            $totalTransfer  = $walletId ? Transaction::where('wallet_id', $walletId)->where('type', 'transfer_out')->where('status', 'success')->sum('amount') : 0;
            $totalPayment   = $walletId ? Transaction::where('wallet_id', $walletId)->where('type', 'payment')->where('status', 'success')->sum('amount') : 0;

            // 5 transaksi terakhir
            $recentTrx = $walletId ? Transaction::where('wallet_id', $walletId)
                ->latest()->limit(5)->get()->map(fn($t) => [
                    'id'          => $t->id,
                    'description' => $t->description,
                    'amount'      => (float) $t->amount,
                    'is_credit'   => in_array($t->type, ['top_up', 'transfer_in']),
                    'created_at'  => $t->created_at,
                ]) : [];

            return [
                'id'                   => $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'phone'                => $user->phone,
                'avatar'               => $user->avatar,
                'created_at'           => $user->created_at,
                'wallet_number'        => $wallet?->wallet_number,
                'wallet_status'        => $wallet?->status ?? 'none',
                'balance'              => (float) ($wallet?->balance ?? 0),
                'pin_set'              => (bool) ($wallet?->pin_set ?? false),
                'daily_transfer_limit' => (float) ($wallet?->daily_transfer_limit ?? 0),
                'daily_topup_limit'    => (float) ($wallet?->daily_topup_limit ?? 0),
                'total_transactions'   => $totalTrx,
                'total_topup'          => (float) $totalTopup,
                'total_transfer_out'   => (float) $totalTransfer,
                'total_payment'        => (float) $totalPayment,
                'recent_transactions'  => $recentTrx,
            ];
        });

        // Stats
        $stats = [
            'total'     => User::where('role', 'user')->count(),
            'active'    => User::where('role', 'user')->whereHas('wallet', fn($q) => $q->where('status', 'active'))->count(),
            'suspended' => User::where('role', 'user')->whereHas('wallet', fn($q) => $q->where('status', 'suspended'))->count(),
            'new_today' => User::where('role', 'user')->whereDate('created_at', Carbon::today())->count(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $data,
            'stats'   => $stats,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/users/{id}/suspend
     */
    public function suspend(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $user   = User::where('role', 'user')->findOrFail($id);
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet tidak ditemukan.'], 404);
        }
        if ($wallet->status === 'suspended') {
            return response()->json(['success' => false, 'message' => 'Wallet sudah disuspend.'], 422);
        }

        $wallet->update(['status' => 'suspended']);

        return response()->json([
            'success' => true,
            'message' => "Wallet {$user->name} berhasil disuspend.",
        ]);
    }

    /**
     * POST /api/admin/users/{id}/unsuspend
     */
    public function unsuspend(Request $request, $id)
    {
        $user   = User::where('role', 'user')->findOrFail($id);
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet tidak ditemukan.'], 404);
        }
        if ($wallet->status === 'active') {
            return response()->json(['success' => false, 'message' => 'Wallet sudah aktif.'], 422);
        }

        $wallet->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => "Wallet {$user->name} berhasil diaktifkan kembali.",
        ]);
    }
}