<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\TransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * GET /api/transfer
     * Riwayat transfer user (baik sebagai pengirim maupun penerima)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $transfers = TransferRequest::where('sender_user_id', $user->id)
            ->orWhere('receiver_user_id', $user->id)
            ->with([
                'senderUser:id,name,avatar',
                'receiverUser:id,name,avatar',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $mapped = $transfers->getCollection()->map(fn($t) => [
            'id'               => $t->id,
            'reference_number' => $t->reference_number,
            'type'             => $t->sender_user_id === $user->id ? 'sent' : 'received',
            'amount'           => (float) $t->amount,
            'fee'              => (float) $t->fee,
            'total_deducted'   => (float) $t->total_deducted,
            'note'             => $t->note,
            'status'           => $t->status,
            'status_label'     => $t->status_label,
            'status_color'     => $t->status_color,
            'sender'           => [
                'name'   => $t->senderUser->name,
                'avatar' => $t->senderUser->avatar,
            ],
            'receiver'         => [
                'name'   => $t->receiverUser->name,
                'avatar' => $t->receiverUser->avatar,
            ],
            'processed_at' => $t->processed_at,
            'created_at'   => $t->created_at,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $mapped,
            'meta'    => [
                'current_page' => $transfers->currentPage(),
                'last_page'    => $transfers->lastPage(),
                'total'        => $transfers->total(),
            ],
        ]);
    }

    /**
     * POST /api/transfer
     * Proses transfer saldo ke wallet lain
     * Menggunakan DB transaction untuk keamanan data
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_wallet_number' => 'required|string',
            'amount'                 => 'required|numeric|min:1000',
            'pin'                    => 'required|string|size:6',
            'note'                   => 'nullable|string|max:255',
        ]);

        $senderUser   = $request->user();
        $senderWallet = $senderUser->wallet;

        // --- Validasi Wallet Pengirim ---
        if (!$senderWallet) {
            return response()->json(['success' => false, 'message' => 'Wallet pengirim tidak ditemukan.'], 404);
        }

        if (!$senderWallet->isActive()) {
            return response()->json(['success' => false, 'message' => 'Wallet kamu sedang tidak aktif.'], 422);
        }

        if (!$senderWallet->pin_set) {
            return response()->json(['success' => false, 'message' => 'Silakan buat PIN terlebih dahulu sebelum transfer.'], 422);
        }

        // Verifikasi PIN
        if (!$senderWallet->verifyPin($request->pin)) {
            return response()->json(['success' => false, 'message' => 'PIN tidak valid.'], 422);
        }

        // --- Validasi Wallet Penerima ---
        if ($senderWallet->wallet_number === $request->receiver_wallet_number) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa transfer ke wallet sendiri.'], 422);
        }

        $receiverWallet = Wallet::where('wallet_number', $request->receiver_wallet_number)
            ->where('status', 'active')
            ->with('user:id,name')
            ->first();

        if (!$receiverWallet) {
            return response()->json(['success' => false, 'message' => 'Nomor wallet tujuan tidak ditemukan atau tidak aktif.'], 404);
        }

        $amount = (float) $request->amount;
        $fee    = 0; // gratis untuk sekarang
        $total  = $amount + $fee;

        // --- Validasi Saldo ---
        if (!$senderWallet->hasSufficientBalance($total)) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi. Saldo kamu: Rp ' . number_format($senderWallet->balance, 0, ',', '.'),
            ], 422);
        }

        // --- Validasi Limit Harian ---
        if ($amount > $senderWallet->daily_transfer_limit) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah melebihi batas transfer harian (Rp ' . number_format($senderWallet->daily_transfer_limit, 0, ',', '.') . ').',
            ], 422);
        }

        // --- Proses Transfer dengan DB Transaction ---
        try {
            $result = DB::transaction(function () use ($senderUser, $senderWallet, $receiverWallet, $amount, $fee, $total, $request) {

                // 1. Buat record TransferRequest
                $transferRequest = TransferRequest::create([
                    'reference_number'   => TransferRequest::generateReferenceNumber(),
                    'sender_user_id'     => $senderUser->id,
                    'sender_wallet_id'   => $senderWallet->id,
                    'receiver_user_id'   => $receiverWallet->user_id,
                    'receiver_wallet_id' => $receiverWallet->id,
                    'amount'             => $amount,
                    'fee'                => $fee,
                    'total_deducted'     => $total,
                    'note'               => $request->note,
                    'status'             => 'pending',
                ]);

                // 2. Kurangi saldo pengirim
                $senderBalanceBefore = $senderWallet->balance;
                $senderWallet->decrement('balance', $total);
                $senderWallet->update(['last_transaction_at' => now()]);

                // 3. Tambah saldo penerima
                $receiverBalanceBefore = $receiverWallet->balance;
                $receiverWallet->increment('balance', $amount);
                $receiverWallet->update(['last_transaction_at' => now()]);

                // 4. Buat transaksi untuk pengirim (transfer_out)
                $senderTrx = Transaction::create([
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'type'               => 'transfer_out',
                    'wallet_id'          => $senderWallet->id,
                    'related_wallet_id'  => $receiverWallet->id,
                    'amount'             => $amount,
                    'balance_before'     => $senderBalanceBefore,
                    'balance_after'      => $senderBalanceBefore - $total,
                    'fee'                => $fee,
                    'status'             => 'success',
                    'description'        => 'Transfer ke ' . $receiverWallet->user->name,
                    'note'               => $request->note,
                    'reference_type'     => 'transfer_request',
                    'reference_id'       => $transferRequest->id,
                ]);

                // 5. Buat transaksi untuk penerima (transfer_in)
                $receiverTrx = Transaction::create([
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'type'               => 'transfer_in',
                    'wallet_id'          => $receiverWallet->id,
                    'related_wallet_id'  => $senderWallet->id,
                    'amount'             => $amount,
                    'balance_before'     => $receiverBalanceBefore,
                    'balance_after'      => $receiverBalanceBefore + $amount,
                    'fee'                => 0,
                    'status'             => 'success',
                    'description'        => 'Transfer dari ' . $senderUser->name,
                    'note'               => $request->note,
                    'reference_type'     => 'transfer_request',
                    'reference_id'       => $transferRequest->id,
                ]);

                // 6. Update TransferRequest dengan transaction IDs
                $transferRequest->update([
                    'status'                  => 'success',
                    'sender_transaction_id'   => $senderTrx->id,
                    'receiver_transaction_id' => $receiverTrx->id,
                    'processed_at'            => now(),
                ]);

                return $transferRequest;
            });

            return response()->json([
                'success' => true,
                'message' => 'Transfer berhasil!',
                'data'    => [
                    'reference_number' => $result->reference_number,
                    'amount'           => $amount,
                    'fee'              => $fee,
                    'receiver_name'    => $receiverWallet->user->name,
                    'receiver_wallet'  => $receiverWallet->wallet_number,
                    'processed_at'     => $result->processed_at,
                ],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer gagal. Silakan coba lagi.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/transfer/{id}
     * Detail satu transfer
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $transfer = TransferRequest::where(function ($q) use ($user) {
                $q->where('sender_user_id', $user->id)
                  ->orWhere('receiver_user_id', $user->id);
            })
            ->with(['senderUser:id,name,avatar', 'receiverUser:id,name,avatar'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $transfer->id,
                'reference_number' => $transfer->reference_number,
                'type'             => $transfer->sender_user_id === $user->id ? 'sent' : 'received',
                'amount'           => (float) $transfer->amount,
                'fee'              => (float) $transfer->fee,
                'total_deducted'   => (float) $transfer->total_deducted,
                'note'             => $transfer->note,
                'status'           => $transfer->status,
                'status_label'     => $transfer->status_label,
                'sender'           => [
                    'name'   => $transfer->senderUser->name,
                    'avatar' => $transfer->senderUser->avatar,
                ],
                'receiver'         => [
                    'name'   => $transfer->receiverUser->name,
                    'avatar' => $transfer->receiverUser->avatar,
                ],
                'processed_at' => $transfer->processed_at,
                'created_at'   => $transfer->created_at,
            ],
        ]);
    }
}