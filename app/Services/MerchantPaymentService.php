<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\MerchantProduct;
use App\Models\MerchantTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MerchantPaymentService
{
    // ── Inquiry (cek tagihan) ─────────────────────────────────────────────────
    public function inquiry(Merchant $merchant, string $inputValue): array
    {
        // Di production, ini akan hit API provider eksternal (PPOB, dll)
        // Untuk sekarang, kembalikan mock data yang realistis
        // Ganti bagian ini dengan integrasi API PPOB yang kamu pilih

        Log::info('MerchantPaymentService: inquiry', [
            'merchant'    => $merchant->code,
            'input_value' => $inputValue,
        ]);

        // Mock response — ganti dengan API call ke provider PPOB
        $mockData = $this->getMockInquiryData($merchant->code, $inputValue);

        if (!$mockData) {
            return ['success' => false, 'message' => 'Nomor pelanggan tidak ditemukan.'];
        }

        return ['success' => true, 'data' => $mockData];
    }

    // ── Process Payment ───────────────────────────────────────────────────────
    public function processPayment(
        User           $user,
        Merchant       $merchant,
        MerchantProduct $product,
        string         $inputValue,
        string         $idempotencyKey,
        Wallet         $wallet,
    ): array {
        // Cek idempotency — cegah double submit
        $existing = MerchantTransaction::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            if ($existing->status === MerchantTransaction::STATUS_SUCCESS) {
                return [
                    'success' => true,
                    'data'    => $this->formatReceipt($existing),
                ];
            }
            return ['success' => false, 'message' => 'Transaksi sedang diproses. Cek riwayat transaksi.'];
        }

        $totalAmount = $product->total_price;

        try {
            return DB::transaction(function () use (
                $user, $merchant, $product, $inputValue,
                $idempotencyKey, $wallet, $totalAmount
            ) {
                // 1. Catat transaksi dengan status pending
                $transaction = MerchantTransaction::create([
                    'user_id'            => $user->id,
                    'merchant_id'        => $merchant->id,
                    'merchant_product_id'=> $product->id,
                    'idempotency_key'    => $idempotencyKey,
                    'input_value'        => $inputValue,
                    'product_price'      => $product->selling_price,
                    'admin_fee'          => $product->admin_fee,
                    'total_amount'       => $totalAmount,
                    'status'             => MerchantTransaction::STATUS_PENDING,
                    'wallet_id'          => $wallet->id,
                    'balance_before'     => $wallet->balance,
                    'balance_after'      => $wallet->balance - $totalAmount,
                ]);

                // 2. Potong saldo wallet
                $wallet->decrement('balance', $totalAmount);

                // 3. Update status ke processing
                $transaction->update(['status' => MerchantTransaction::STATUS_PROCESSING]);

                // 4. Hit API provider (mock untuk sekarang)
                $providerResult = $this->callProvider($merchant, $product, $inputValue);

                if ($providerResult['success']) {
                    // 5a. Sukses — tandai transaksi berhasil
                    $receiptData = $this->buildReceiptData($merchant, $product, $inputValue, $providerResult);
                    $transaction->markAsSuccess(
                        providerReference: $providerResult['reference'],
                        receiptData:       $receiptData,
                    );

                    Log::info('MerchantPaymentService: payment success', [
                        'transaction_id' => $transaction->id,
                        'merchant'       => $merchant->code,
                        'amount'         => $totalAmount,
                    ]);

                    return ['success' => true, 'data' => $this->formatReceipt($transaction->fresh())];

                } else {
                    // 5b. Gagal — kembalikan saldo & tandai failed
                    $wallet->increment('balance', $totalAmount);
                    $transaction->markAsFailed($providerResult['message']);

                    Log::warning('MerchantPaymentService: payment failed', [
                        'transaction_id' => $transaction->id,
                        'reason'         => $providerResult['message'],
                    ]);

                    return ['success' => false, 'message' => $providerResult['message']];
                }
            });

        } catch (\Exception $e) {
            Log::error('MerchantPaymentService: exception', [
                'error'          => $e->getMessage(),
                'idempotency_key'=> $idempotencyKey,
            ]);

            return ['success' => false, 'message' => 'Terjadi kesalahan sistem. Coba lagi.'];
        }
    }

    // ── Provider API call (mock — ganti dengan integrasi PPOB) ───────────────
    private function callProvider(Merchant $merchant, MerchantProduct $product, string $inputValue): array
    {
        // TODO: Ganti dengan HTTP call ke provider PPOB pilihan kamu
        // Contoh provider yang umum dipakai di Indonesia:
        // - Digiflazz   (https://developer.digiflazz.com)
        // - iReap / Raja Pulsa
        // - Tokopayment

        // Mock success untuk development
        return [
            'success'   => true,
            'reference' => 'TRX-' . strtoupper(substr(md5(uniqid()), 0, 10)),
            'message'   => 'Transaksi berhasil diproses.',
            'sn'        => $this->generateMockSn($merchant->code),
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function getMockInquiryData(string $merchantCode, string $inputValue): ?array
    {
        return match ($merchantCode) {
            'PLN_POST' => [
                'customer_name'  => 'NAMA PELANGGAN PLN',
                'customer_id'    => $inputValue,
                'bill_amount'    => 150000,
                'admin_fee'      => 2500,
                'total_amount'   => 152500,
                'bill_period'    => date('F Y', strtotime('-1 month')),
                'stand_meter'    => ['before' => 1200, 'after' => 1350],
            ],
            'BPJS' => [
                'customer_name'  => 'NAMA PESERTA BPJS',
                'customer_id'    => $inputValue,
                'bill_amount'    => 75000,
                'admin_fee'      => 2500,
                'total_amount'   => 77500,
                'bill_period'    => date('F Y'),
                'member_count'   => 3,
            ],
            'PDAM' => [
                'customer_name'  => 'NAMA PELANGGAN PDAM',
                'customer_id'    => $inputValue,
                'bill_amount'    => 85000,
                'admin_fee'      => 2500,
                'total_amount'   => 87500,
                'bill_period'    => date('F Y', strtotime('-1 month')),
                'usage_m3'       => 12,
            ],
            default => null,
        };
    }

    private function generateMockSn(string $merchantCode): string
    {
        return match (true) {
            str_contains($merchantCode, 'PLN') => implode('-', str_split(str_pad((string)random_int(100000000, 999999999), 20, '0', STR_PAD_LEFT), 4)),
            default => strtoupper(substr(md5(uniqid()), 0, 16)),
        };
    }

    private function buildReceiptData(
        Merchant        $merchant,
        MerchantProduct $product,
        string          $inputValue,
        array           $providerResult,
    ): array {
        return [
            'merchant_name'    => $merchant->name,
            'merchant_code'    => $merchant->code,
            'product_name'     => $product->name,
            'input_value'      => $inputValue,
            'selling_price'    => (float) $product->selling_price,
            'admin_fee'        => (float) $product->admin_fee,
            'total_amount'     => (float) $product->total_price,
            'reference'        => $providerResult['reference'],
            'sn'               => $providerResult['sn'] ?? null,
            'processed_at'     => now()->toISOString(),
        ];
    }

    private function formatReceipt(MerchantTransaction $t): array
    {
        return [
            'transaction_id'   => $t->id,
            'status'           => $t->status,
            'status_label'     => $t->status_label,
            'status_color'     => $t->status_color,
            'merchant_name'    => $t->merchant->name ?? '',
            'product_name'     => $t->product->name  ?? '',
            'input_value'      => $t->input_value,
            'total_amount'     => (float) $t->total_amount,
            'admin_fee'        => (float) $t->admin_fee,
            'provider_reference' => $t->provider_reference,
            'receipt_data'     => $t->receipt_data,
            'created_at'       => $t->created_at,
        ];
    }
}