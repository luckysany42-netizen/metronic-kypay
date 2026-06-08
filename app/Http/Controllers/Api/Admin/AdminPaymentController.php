<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TopUpRequest;
use App\Models\TransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPaymentController extends Controller
{
    // =====================================================
    // DASHBOARD STATS
    // =====================================================

    public function dashboard()
    {
        $stats = [
            'total_wallets'           => Wallet::count(),
            'active_wallets'          => Wallet::where('status', 'active')->count(),
            'suspended_wallets'       => Wallet::where('status', 'suspended')->count(),
            'total_balance_in_system' => Wallet::sum('balance'),
            'total_transactions'      => Transaction::count(),
            'total_topup_amount'      => Transaction::where('type', 'top_up')->where('status', 'success')->sum('amount'),
            'total_transfer_amount'   => Transaction::where('type', 'transfer_out')->where('status', 'success')->sum('amount'),
            'pending_topup'           => TopUpRequest::where('status', 'pending')->count(),
            'approved_topup_today'    => TopUpRequest::where('status', 'approved')->whereDate('reviewed_at', today())->count(),
            'total_transfers_today'   => TransferRequest::where('status', 'success')->whereDate('processed_at', today())->count(),
            'recent_topup_requests'   => TopUpRequest::with('user:id,name,avatar')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(fn($r) => [
                    'id'               => $r->id,
                    'reference_number' => $r->reference_number,
                    'user_name'        => $r->user->name,
                    'user_avatar'      => $r->user->avatar,
                    'amount'           => (float) $r->amount,
                    'created_at'       => $r->created_at,
                ]),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    // =====================================================
    // CHART DATA
    // =====================================================

    public function chartData(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $days = in_array($days, [7, 14, 30]) ? $days : 7;

        $labels       = [];
        $topupData    = [];
        $transferData = [];
        $paymentData  = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            $labels[]       = now()->subDays($i)->locale('id')->isoFormat('DD MMM');
            $topupData[]    = Transaction::where('type', 'top_up')
                                ->where('status', 'success')
                                ->whereDate('created_at', $date)
                                ->count();
            $transferData[] = Transaction::where('type', 'transfer_out')
                                ->where('status', 'success')
                                ->whereDate('created_at', $date)
                                ->count();
            $paymentData[]  = Transaction::where('type', 'payment')
                                ->where('status', 'success')
                                ->whereDate('created_at', $date)
                                ->count();
        }

        // Donut: total per tipe semua waktu
        $totalTopup    = Transaction::where('type', 'top_up')->where('status', 'success')->count();
        $totalTransfer = Transaction::where('type', 'transfer_out')->where('status', 'success')->count();
        $totalPayment  = Transaction::where('type', 'payment')->where('status', 'success')->count();
        $totalOther    = Transaction::whereNotIn('type', ['top_up', 'transfer_out', 'payment'])->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'labels'   => $labels,
                'topup'    => $topupData,
                'transfer' => $transferData,
                'payment'  => $paymentData,
                'donut'    => [
                    'top_up'   => $totalTopup,
                    'transfer' => $totalTransfer,
                    'payment'  => $totalPayment,
                    'other'    => $totalOther,
                ],
            ],
        ]);
    }

    // =====================================================
    // WALLET MANAGEMENT
    // =====================================================

    public function wallets(Request $request)
    {
        $query = Wallet::with('user:id,name,email,avatar')
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('wallet_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $wallets = $query->paginate(15);

        $mapped = $wallets->getCollection()->map(fn($w) => [
            'id'             => $w->id,
            'wallet_number'  => $w->wallet_number,
            'wallet_name'    => $w->wallet_name,
            'balance'        => (float) $w->balance,
            'locked_balance' => (float) $w->locked_balance,
            'status'         => $w->status,
            'pin_set'        => $w->pin_set,
            'user'           => [
                'id'     => $w->user->id,
                'name'   => $w->user->name,
                'email'  => $w->user->email,
                'avatar' => $w->user->avatar,
            ],
            'last_transaction_at' => $w->last_transaction_at,
            'created_at'          => $w->created_at,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $mapped,
            'meta'    => [
                'current_page' => $wallets->currentPage(),
                'last_page'    => $wallets->lastPage(),
                'total'        => $wallets->total(),
            ],
        ]);
    }

    public function updateWalletStatus(Request $request, int $id)
    {
        $request->validate(['status' => 'required|in:active,suspended,closed']);

        $wallet = Wallet::findOrFail($id);
        $wallet->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status wallet berhasil diperbarui menjadi ' . $request->status . '.',
            'data'    => ['status' => $wallet->status],
        ]);
    }

    public function updateWalletLimit(Request $request, int $id)
    {
        $request->validate([
            'daily_transfer_limit' => 'nullable|numeric|min:0',
            'daily_topup_limit'    => 'nullable|numeric|min:0',
        ]);

        $wallet = Wallet::findOrFail($id);
        $wallet->update($request->only(['daily_transfer_limit', 'daily_topup_limit']));

        return response()->json(['success' => true, 'message' => 'Limit wallet berhasil diperbarui.']);
    }

    // =====================================================
    // PAYMENT METHODS MANAGEMENT
    // =====================================================

    public function paymentMethods()
    {
        $methods = PaymentMethod::with('creator:id,name')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => [
                'id'             => $m->id,
                'name'           => $m->name,
                'code'           => $m->code,
                'type'           => $m->type,
                'type_label'     => $m->type_label,
                'account_number' => $m->account_number,
                'account_name'   => $m->account_name,
                'account_bank'   => $m->account_bank,
                'logo'           => $m->logo ? Storage::disk('public')->url($m->logo) : null,
                'instructions'   => $m->instructions,
                'color'          => $m->color,
                'min_amount'     => (float) $m->min_amount,
                'max_amount'     => (float) $m->max_amount,
                'is_active'      => $m->is_active,
                'sort_order'     => $m->sort_order,
                'created_by'     => $m->creator?->name,
                'created_at'     => $m->created_at,
            ]);

        return response()->json(['success' => true, 'data' => $methods]);
    }

    public function storePaymentMethod(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'required|string|max:20|unique:payment_methods,code',
            'type'           => 'required|in:bank_transfer,e_wallet,other',
            'account_number' => 'nullable|string|max:50',
            'account_name'   => 'nullable|string|max:100',
            'account_bank'   => 'nullable|string|max:100',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png,svg|max:1024',
            'instructions'   => 'nullable|string',
            'color'          => 'nullable|string|max:10',
            'min_amount'     => 'nullable|numeric|min:0',
            'max_amount'     => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
            'sort_order'     => 'nullable|integer',
        ]);

        $data = $request->except('logo');
        $data['created_by'] = auth()->id();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('kypay/payment-logos', 'public');
        }

        $method = PaymentMethod::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran berhasil ditambahkan.',
            'data'    => ['id' => $method->id, 'name' => $method->name],
        ], 201);
    }

    public function updatePaymentMethod(Request $request, int $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $request->validate([
            'name'           => 'string|max:100',
            'code'           => 'string|max:20|unique:payment_methods,code,' . $id,
            'type'           => 'in:bank_transfer,e_wallet,other',
            'account_number' => 'nullable|string|max:50',
            'account_name'   => 'nullable|string|max:100',
            'account_bank'   => 'nullable|string|max:100',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png,svg|max:1024',
            'instructions'   => 'nullable|string',
            'color'          => 'nullable|string|max:10',
            'min_amount'     => 'nullable|numeric|min:0',
            'max_amount'     => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
            'sort_order'     => 'nullable|integer',
        ]);

        $data = $request->except(['logo', 'remove_logo']);

        if ($request->input('remove_logo') == '1') {
            if ($method->logo) {
                Storage::disk('public')->delete($method->logo);
            }
            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($method->logo) {
                Storage::disk('public')->delete($method->logo);
            }
            $data['logo'] = $request->file('logo')->store('kypay/payment-logos', 'public');
        }

        $method->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran berhasil diperbarui.',
        ]);
    }

    public function destroyPaymentMethod(int $id)
    {
        $method = PaymentMethod::findOrFail($id);

        if ($method->logo) {
            Storage::disk('public')->delete($method->logo);
        }

        $method->delete();

        return response()->json(['success' => true, 'message' => 'Metode pembayaran berhasil dihapus.']);
    }

    // =====================================================
    // ALL TRANSACTIONS (ADMIN VIEW)
    // =====================================================

    public function transactions(Request $request)
    {
        $query = Transaction::with('wallet.user:id,name,avatar')
            ->orderBy('created_at', 'desc');

        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20);

        $mapped = $transactions->getCollection()->map(fn($t) => [
            'id'                 => $t->id,
            'transaction_number' => $t->transaction_number,
            'type'               => $t->type,
            'type_label'         => $t->type_label,
            'amount'             => (float) $t->amount,
            'balance_before'     => (float) $t->balance_before,
            'balance_after'      => (float) $t->balance_after,
            'status'             => $t->status,
            'description'        => $t->description,
            'user_name'          => $t->wallet->user->name,
            'user_avatar'        => $t->wallet->user->avatar,
            'wallet_number'      => $t->wallet->wallet_number,
            'created_at'         => $t->created_at,
        ]);

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
}