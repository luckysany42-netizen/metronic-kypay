<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrPayment;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QrPaymentController extends Controller
{
    // =====================================================
    // POST /api/qr-payment/generate
    // Generate QR — bisa untuk terima pembayaran biasa
    // ATAU untuk Bayar & Beli (sertakan product_code + target_number)
    // =====================================================
    public function generate(Request $request)
    {
        $request->validate([
            'amount'        => 'required|numeric|min:1000',
            'description'   => 'nullable|string|max:255',
            'product_code'  => 'nullable|string|max:100',
            'target_number' => 'nullable|string|max:50',
        ]);

        $merchant = $request->user();
        $wallet   = $merchant->wallet;

        if (!$wallet || $wallet->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Wallet tidak aktif.'], 422);
        }

        // Expire QR pending lama milik user ini
        QrPayment::where('merchant_id', $merchant->id)
                  ->where('status', 'pending')
                  ->where('expires_at', '<', now())
                  ->update(['status' => 'expired']);

        $token = Str::random(48);

        $qrPayment = QrPayment::create([
            'qr_token'      => $token,
            'merchant_id'   => $merchant->id,
            'amount'        => $request->amount,
            'description'   => $request->description,
            'product_code'  => $request->product_code,
            'target_number' => $request->target_number,
            'status'        => 'pending',
            'expires_at'    => now()->addMinutes(5),
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'qr_token'      => $token,
                'amount'        => (float) $qrPayment->amount,
                'description'   => $qrPayment->description,
                'product_code'  => $qrPayment->product_code,
                'target_number' => $qrPayment->target_number,
                'expires_at'    => $qrPayment->expires_at,
                'expires_in'    => 300,
                'merchant'      => [
                    'name'   => $merchant->name,
                    'avatar' => $merchant->avatar ?? null,
                ],
            ],
        ]);
    }

    // =====================================================
    // GET /api/qr-payment/detail/{token}
    // =====================================================
    public function detail(string $token)
    {
        $qr = QrPayment::with('merchant:id,name,avatar')
                        ->where('qr_token', $token)
                        ->first();

        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR tidak ditemukan.'], 404);
        }

        if ($qr->status === 'pending' && $qr->isExpired()) {
            $qr->update(['status' => 'expired']);
        }

        if ($qr->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'QR sudah ' . $qr->status . '.',
                'status'  => $qr->status,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'qr_token'        => $qr->qr_token,
                'amount'          => (float) $qr->amount,
                'description'     => $qr->description,
                'product_code'    => $qr->product_code,
                'target_number'   => $qr->target_number,
                'expires_at'      => $qr->expires_at,
                'status'          => $qr->status,
                'merchant_name'   => $qr->merchant->name,
                'merchant_avatar' => $qr->merchant->avatar ?? null,
                'is_bill_payment' => !empty($qr->product_code), // flag untuk frontend
            ],
        ]);
    }

    // =====================================================
    // POST /api/qr-payment/pay
    // User scan & bayar QR
    // Kalau QR punya product_code → otomatis proses produk juga
    // =====================================================
    public function pay(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'pin'      => 'required|string|size:6',
        ]);

        $payer = $request->user();

        try {
            $result = DB::transaction(function () use ($request, $payer) {

                $qr = QrPayment::where('qr_token', $request->qr_token)
                                ->lockForUpdate()
                                ->first();

                if (!$qr) throw new \Exception('QR tidak ditemukan.');
                if ($qr->status !== 'pending') throw new \Exception('QR sudah ' . $qr->status . '.');
                if ($qr->isExpired()) {
                    $qr->update(['status' => 'expired']);
                    throw new \Exception('QR sudah expired.');
                }
                // Validasi tidak bisa bayar QR sendiri — HANYA untuk QR transfer/terima biasa
                // QR dari Bayar & Beli (product_code ada) boleh dibayar sendiri
                if ($qr->merchant_id === $payer->id && empty($qr->product_code)) {
                    throw new \Exception('Tidak bisa membayar QR milik sendiri.');
                }

                $payerWallet = Wallet::where('user_id', $payer->id)
                                     ->lockForUpdate()->first();

                if (!$payerWallet || $payerWallet->status !== 'active') {
                    throw new \Exception('Wallet tidak aktif.');
                }
                if (!$payerWallet->verifyPin($request->pin)) {
                    throw new \Exception('PIN tidak valid.');
                }
                if ($payerWallet->balance < $qr->amount) {
                    throw new \Exception('Saldo tidak cukup.');
                }

                $merchantWallet = Wallet::where('user_id', $qr->merchant_id)
                                        ->lockForUpdate()->first();

                if (!$merchantWallet) throw new \Exception('Wallet merchant tidak ditemukan.');

                $balanceBefore = $payerWallet->balance;

                // Potong saldo payer
                $payerWallet->decrement('balance', $qr->amount);
                $payerWallet->update(['last_transaction_at' => now()]);

                // Tambah saldo merchant
                $merchantWallet->increment('balance', $qr->amount);
                $merchantWallet->update(['last_transaction_at' => now()]);

                $trxNumber = Transaction::generateTransactionNumber();

                // Tentukan deskripsi transaksi
                $isBillPayment = !empty($qr->product_code);
                $description   = $isBillPayment
                    ? 'QR Bayar & Beli - ' . ($qr->description ?? $qr->product_code)
                    : 'QR Payment - ' . ($qr->description ?? $qr->merchant->name);

                // Catat transaksi payer (debit)
                $transaction = Transaction::create([
                    'wallet_id'          => $payerWallet->id,
                    'related_wallet_id'  => $merchantWallet->id,
                    'transaction_number' => $trxNumber,
                    'type'               => 'payment',
                    'amount'             => $qr->amount,
                    'fee'                => 0,
                    'balance_before'     => $balanceBefore,
                    'balance_after'      => $payerWallet->fresh()->balance,
                    'status'             => 'success',
                    'description'        => $description,
                    'reference_type'     => 'qr_payment',
                    'reference_id'       => $qr->id,
                ]);

                // Catat transaksi merchant (kredit)
                Transaction::create([
                    'wallet_id'          => $merchantWallet->id,
                    'related_wallet_id'  => $payerWallet->id,
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'type'               => 'transfer_in',
                    'amount'             => $qr->amount,
                    'fee'                => 0,
                    'balance_before'     => $merchantWallet->balance - $qr->amount,
                    'balance_after'      => $merchantWallet->balance,
                    'status'             => 'success',
                    'description'        => $isBillPayment
                        ? 'QR Bayar & Beli diterima dari ' . $payer->name
                        : 'QR Payment diterima dari ' . $payer->name,
                    'reference_type'     => 'qr_payment',
                    'reference_id'       => $qr->id,
                ]);

                // Proses produk jika ini Bayar & Beli via QR
                $billResult = null;
                if ($isBillPayment) {
                    $product = DB::table('bill_products')
                        ->where('code', $qr->product_code)
                        ->where('is_active', true)
                        ->first();

                    if ($product) {
                        // Generate kode hasil (token listrik / voucher / dll)
                        $resultCode = strtoupper(substr($product->category, 0, 3))
                            . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

                        $billResult = [
                            'product_name'  => $product->name,
                            'provider'      => $product->provider,
                            'category'      => $product->category,
                            'target_number' => $qr->target_number,
                            'result_code'   => $resultCode,
                        ];
                    }
                }

                // Update status QR → paid
                $qr->update([
                    'status'         => 'paid',
                    'payer_id'       => $payer->id,
                    'paid_at'        => now(),
                    'transaction_id' => $transaction->id,
                ]);

                return [
                    'transaction_number' => $trxNumber,
                    'amount'             => (float) $qr->amount,
                    'merchant_name'      => $qr->merchant->name,
                    'description'        => $qr->description,
                    'is_bill_payment'    => $isBillPayment,
                    'bill'               => $billResult,
                    'paid_at'            => now(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil.',
                'data'    => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // =====================================================
    // GET /api/qr-payment/status/{token}
    // Polling — merchant/user menunggu pembayaran
    // =====================================================
    public function status(string $token)
    {
        $qr = QrPayment::where('qr_token', $token)->first();

        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR tidak ditemukan.'], 404);
        }

        if ($qr->status === 'pending' && $qr->isExpired()) {
            $qr->update(['status' => 'expired']);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'status'      => $qr->status,
                'amount'      => (float) $qr->amount,
                'description' => $qr->description,
                'paid_at'     => $qr->paid_at,
                'expires_at'  => $qr->expires_at,
                'bill'        => $qr->status === 'paid' && $qr->product_code
                    ? ['product_code' => $qr->product_code, 'target_number' => $qr->target_number]
                    : null,
            ],
        ]);
    }

    // =====================================================
    // DELETE /api/qr-payment/{token}/cancel
    // =====================================================
    public function cancel(Request $request, string $token)
    {
        $qr = QrPayment::where('qr_token', $token)
                        ->where('merchant_id', $request->user()->id)
                        ->first();

        if (!$qr) {
            return response()->json(['success' => false, 'message' => 'QR tidak ditemukan.'], 404);
        }
        if ($qr->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'QR tidak bisa dibatalkan.'], 422);
        }

        $qr->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'message' => 'QR berhasil dibatalkan.']);
    }
}