<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TopUpRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TopUpController extends Controller
{
    /**
     * GET /api/topup
     * Daftar semua pengajuan top up milik user yang login
     */
    public function index(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet tidak ditemukan.'], 404);
        }

        $requests = $wallet->topUpRequests()
            ->with('reviewer:id,name')
            ->paginate(10);

        $mapped = $requests->getCollection()->map(fn($r) => [
            'id'               => $r->id,
            'reference_number' => $r->reference_number,
            'amount'           => (float) $r->amount,
            'payment_method'   => $r->payment_method,
            'proof_image'      => $r->proof_image ? Storage::url($r->proof_image) : null,
            'user_note'        => $r->user_note,
            'status'           => $r->status,
            'status_label'     => $r->status_label,
            'status_color'     => $r->status_color,
            'admin_note'       => $r->admin_note,
            'reviewer'         => $r->reviewer?->name,
            'reviewed_at'      => $r->reviewed_at,
            'created_at'       => $r->created_at,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $mapped,
            'meta'    => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'total'        => $requests->total(),
            ],
        ]);
    }

    /**
     * POST /api/topup
     * User mengajukan top up baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:10000|max:50000000',
            'payment_method'  => 'required|string|max:100',
            'payment_account' => 'nullable|string|max:50',
            'payment_holder'  => 'nullable|string|max:100',
            'proof_image'     => 'required|image|mimes:jpg,jpeg,png|max:5120', // max 5MB
            'user_note'       => 'nullable|string|max:500',
        ]);

        $user   = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet tidak ditemukan.'], 404);
        }

        if (!$wallet->isActive()) {
            return response()->json(['success' => false, 'message' => 'Wallet kamu sedang tidak aktif.'], 422);
        }

        // Cek apakah ada top up pending yang belum selesai
        $hasPending = TopUpRequest::where('wallet_id', $wallet->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu masih memiliki pengajuan top up yang belum diproses. Tunggu konfirmasi admin.',
            ], 422);
        }

        // Upload bukti transfer
        $proofPath = $request->file('proof_image')->store('kypay/topup-proofs', 'public');

        $topUp = TopUpRequest::create([
            'user_id'         => $user->id,
            'wallet_id'       => $wallet->id,
            'reference_number'=> TopUpRequest::generateReferenceNumber(),
            'amount'          => $request->amount,
            'payment_method'  => $request->payment_method,
            'payment_account' => $request->payment_account,
            'payment_holder'  => $request->payment_holder,
            'proof_image'     => $proofPath,
            'user_note'       => $request->user_note,
            'status'          => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan top up berhasil dikirim. Menunggu konfirmasi admin.',
            'data'    => [
                'reference_number' => $topUp->reference_number,
                'amount'           => (float) $topUp->amount,
                'status'           => $topUp->status,
            ],
        ], 201);
    }

    /**
     * GET /api/topup/{id}
     * Detail pengajuan top up
     */
    public function show(Request $request, int $id)
    {
        $wallet = $request->user()->wallet;
        $topUp  = TopUpRequest::where('wallet_id', $wallet?->id)
            ->with('reviewer:id,name')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $topUp->id,
                'reference_number' => $topUp->reference_number,
                'amount'           => (float) $topUp->amount,
                'payment_method'   => $topUp->payment_method,
                'payment_account'  => $topUp->payment_account,
                'payment_holder'   => $topUp->payment_holder,
                'proof_image'      => $topUp->proof_image ? Storage::url($topUp->proof_image) : null,
                'user_note'        => $topUp->user_note,
                'status'           => $topUp->status,
                'status_label'     => $topUp->status_label,
                'status_color'     => $topUp->status_color,
                'admin_note'       => $topUp->admin_note,
                'reviewer'         => $topUp->reviewer?->name,
                'reviewed_at'      => $topUp->reviewed_at,
                'created_at'       => $topUp->created_at,
            ],
        ]);
    }

    /**
     * GET /api/topup/payment-methods
     * Daftar metode pembayaran aktif yang tersedia
     */
    public function paymentMethods()
    {
        $methods = PaymentMethod::active()->get()->map(fn($m) => [
            'id'             => $m->id,
            'name'           => $m->name,
            'code'           => $m->code,
            'type'           => $m->type,
            'type_label'     => $m->type_label,
            'account_number' => $m->account_number,
            'account_name'   => $m->account_name,
            'account_bank'   => $m->account_bank,
            'logo'           => $m->logo ? Storage::url($m->logo) : null,
            'instructions'   => $m->instructions,
            'color'          => $m->color,
            'min_amount'     => (float) $m->min_amount,
            'max_amount'     => (float) $m->max_amount,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $methods,
        ]);
    }
}