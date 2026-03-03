<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopUpRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminTopUpController extends Controller
{
    /**
     * GET /api/admin/topup
     * Semua pengajuan top up (dengan filter status)
     */
    public function index(Request $request)
    {
        $query = TopUpRequest::with([
            'user:id,name,email,avatar',
            'wallet:id,wallet_number,balance',
            'reviewer:id,name',
        ])->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->status && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by reference number atau nama user
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->paginate(15);

        $mapped = $requests->getCollection()->map(fn($r) => [
            'id'               => $r->id,
            'reference_number' => $r->reference_number,
            'user'             => [
                'id'     => $r->user->id,
                'name'   => $r->user->name,
                'email'  => $r->user->email,
                'avatar' => $r->user->avatar,
            ],
            'wallet_number'  => $r->wallet->wallet_number,
            'amount'         => (float) $r->amount,
            'payment_method' => $r->payment_method,
            'proof_image'    => $r->proof_image ? Storage::url($r->proof_image) : null,
            'user_note'      => $r->user_note,
            'status'         => $r->status,
            'status_label'   => $r->status_label,
            'status_color'   => $r->status_color,
            'admin_note'     => $r->admin_note,
            'reviewer'       => $r->reviewer?->name,
            'reviewed_at'    => $r->reviewed_at,
            'created_at'     => $r->created_at,
        ]);

        // Summary stats
        $stats = [
            'total_pending'  => TopUpRequest::pending()->count(),
            'total_approved' => TopUpRequest::approved()->count(),
            'total_rejected' => TopUpRequest::rejected()->count(),
            'total_amount_approved' => TopUpRequest::approved()->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $mapped,
            'stats'   => $stats,
            'meta'    => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'total'        => $requests->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/topup/{id}
     * Detail satu pengajuan top up
     */
    public function show(int $id)
    {
        $topUp = TopUpRequest::with([
            'user:id,name,email,phone,avatar',
            'wallet:id,wallet_number,balance,status',
            'reviewer:id,name',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $topUp->id,
                'reference_number' => $topUp->reference_number,
                'user'             => $topUp->user,
                'wallet'           => [
                    'wallet_number' => $topUp->wallet->wallet_number,
                    'balance'       => (float) $topUp->wallet->balance,
                    'status'        => $topUp->wallet->status,
                ],
                'amount'          => (float) $topUp->amount,
                'payment_method'  => $topUp->payment_method,
                'payment_account' => $topUp->payment_account,
                'payment_holder'  => $topUp->payment_holder,
                'proof_image'     => $topUp->proof_image ? Storage::url($topUp->proof_image) : null,
                'user_note'       => $topUp->user_note,
                'status'          => $topUp->status,
                'status_label'    => $topUp->status_label,
                'admin_note'      => $topUp->admin_note,
                'reviewer'        => $topUp->reviewer?->name,
                'reviewed_at'     => $topUp->reviewed_at,
                'created_at'      => $topUp->created_at,
            ],
        ]);
    }

    /**
     * POST /api/admin/topup/{id}/approve
     * Admin menyetujui pengajuan top up → saldo wallet user bertambah
     */
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $topUp = TopUpRequest::with('wallet')->findOrFail($id);

        if (!$topUp->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ini sudah diproses sebelumnya (status: ' . $topUp->status . ').',
            ], 422);
        }

        try {
            DB::transaction(function () use ($topUp, $request) {
                $wallet       = $topUp->wallet;
                $balanceBefore = $wallet->balance;

                // 1. Tambah saldo wallet
                $wallet->increment('balance', $topUp->amount);
                $wallet->update(['last_transaction_at' => now()]);

                // 2. Buat record transaction
                $transaction = Transaction::create([
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'type'               => 'top_up',
                    'wallet_id'          => $wallet->id,
                    'related_wallet_id'  => null,
                    'amount'             => $topUp->amount,
                    'balance_before'     => $balanceBefore,
                    'balance_after'      => $balanceBefore + $topUp->amount,
                    'fee'                => 0,
                    'status'             => 'success',
                    'description'        => 'Top Up via ' . $topUp->payment_method,
                    'reference_type'     => 'top_up_request',
                    'reference_id'       => $topUp->id,
                ]);

                // 3. Update status top up request
                $topUp->update([
                    'status'         => 'approved',
                    'reviewed_by'    => auth()->id(),
                    'reviewed_at'    => now(),
                    'admin_note'     => $request->admin_note,
                    'transaction_id' => $transaction->id,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Top up berhasil disetujui. Saldo user sudah ditambahkan.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses persetujuan.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/admin/topup/{id}/reject
     * Admin menolak pengajuan top up
     */
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:500', // wajib isi alasan penolakan
        ]);

        $topUp = TopUpRequest::findOrFail($id);

        if (!$topUp->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ini sudah diproses sebelumnya (status: ' . $topUp->status . ').',
            ], 422);
        }

        $topUp->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note'  => $request->admin_note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan top up telah ditolak.',
        ]);
    }
}