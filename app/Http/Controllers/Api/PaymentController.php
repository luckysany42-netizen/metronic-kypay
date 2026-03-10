<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PaymentController extends Controller
{
    /**
     * GET /api/payment/products
     * List semua produk per kategori
     */
    public function products(Request $request)
    {
        $category = $request->query('category');

        $query = DB::table('bill_products')->where('is_active', true);

        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('sort_order')->orderBy('price')->get();

        // Group by category kalau tidak ada filter
        if (!$category) {
            $grouped = $products->groupBy('category')->map(fn($items, $cat) => [
                'category'       => $cat,
                'category_label' => $this->categoryLabel($cat),
                'icon'           => $items->first()->icon,
                'color'          => $items->first()->color,
                'products'       => $items->values(),
            ])->values();

            return response()->json(['success' => true, 'data' => $grouped]);
        }

        return response()->json(['success' => true, 'data' => $products->values()]);
    }

    /**
     * POST /api/payment
     * Proses pembayaran tagihan
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_code'  => 'required|string',
            'target_number' => 'required|string|max:20', // nomor HP / ID meter / no BPJS / ID game
            'pin'           => 'required|string|size:6',
            'note'          => 'nullable|string|max:255',
        ]);

        $user   = $request->user();
        $wallet = $user->wallet;

        // Validasi wallet
        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet tidak ditemukan.'], 404);
        }
        if (!$wallet->isActive()) {
            return response()->json(['success' => false, 'message' => 'Wallet kamu tidak aktif.'], 422);
        }
        if (!$wallet->pin_set) {
            return response()->json(['success' => false, 'message' => 'Buat PIN terlebih dahulu.'], 422);
        }
        if (!$wallet->verifyPin($request->pin)) {
            return response()->json(['success' => false, 'message' => 'PIN tidak valid.'], 422);
        }

        // Cari produk
        $product = DB::table('bill_products')
            ->where('code', $request->product_code)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan.'], 404);
        }

        $amount = (float) $product->price;

        // Cek saldo
        if (!$wallet->hasSufficientBalance($amount)) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi. Saldo kamu: Rp ' . number_format($wallet->balance, 0, ',', '.'),
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($user, $wallet, $product, $amount, $request) {
                $balanceBefore = $wallet->balance;

                // Kurangi saldo
                $wallet->decrement('balance', $amount);
                $wallet->update(['last_transaction_at' => now()]);

                // Buat token/kode hasil pembayaran (dummy)
                $resultCode = strtoupper(substr($product->category, 0, 3)) . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

                // Catat transaksi
                $trx = Transaction::create([
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'type'               => 'payment',
                    'wallet_id'          => $wallet->id,
                    'related_wallet_id'  => null,
                    'amount'             => $amount,
                    'balance_before'     => $balanceBefore,
                    'balance_after'      => $balanceBefore - $amount,
                    'fee'                => 0,
                    'status'             => 'success',
                    'description'        => $product->name . ' - ' . $request->target_number,
                    'note'               => $request->note,
                    'reference_type'     => 'bill_payment',
                    'reference_id'       => null,
                ]);

                return [
                    'transaction_number' => $trx->transaction_number,
                    'product_name'       => $product->name,
                    'provider'           => $product->provider,
                    'category'           => $product->category,
                    'target_number'      => $request->target_number,
                    'amount'             => $amount,
                    'result_code'        => $resultCode, // token listrik / serial voucher dll
                    'processed_at'       => now(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil!',
                'data'    => $result,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran gagal. Silakan coba lagi.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function categoryLabel(string $category): string
    {   
        return match($category) {
            'pulsa'          => 'Pulsa',
            'paket_data'     => 'Paket Data',
            'token_listrik'  => 'Token Listrik',
            'bpjs'           => 'BPJS Kesehatan',
            'voucher_game'   => 'Voucher Game',
            default          => ucfirst($category),
        };
    }
}