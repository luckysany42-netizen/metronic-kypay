<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantCategory;
use App\Models\MerchantProduct;
use App\Models\MerchantTransaction;
use App\Services\MerchantPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function __construct(
        private readonly MerchantPaymentService $paymentService
    ) {}

    // ── GET /merchant/categories ──────────────────────────────────────────────
    public function categories(): JsonResponse
    {
        $categories = MerchantCategory::active()
            ->withCount(['activeMerchants'])
            ->get()
            ->map(fn($cat) => [
                'id'             => $cat->id,
                'code'           => $cat->code,
                'name'           => $cat->name,
                'icon_url'       => $cat->icon_url,
                'color_hex'      => $cat->color_hex,
                'merchant_count' => $cat->active_merchants_count,
            ]);

        return response()->json(['status' => true, 'data' => $categories]);
    }

    // ── GET /merchant/featured ────────────────────────────────────────────────
    public function featured(): JsonResponse
    {
        $merchants = Merchant::featured()
            ->with('category:id,code,name,color_hex')
            ->get()
            ->map(fn($m) => $this->formatMerchant($m));

        return response()->json(['status' => true, 'data' => $merchants]);
    }

    // ── GET /merchant?category_id=&search= ───────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Merchant::active()->with('category:id,code,name,color_hex');

        if ($request->filled('category_id')) {
            $query->where('merchant_category_id', $request->category_id);
        }

        if ($request->filled('category_code')) {
            $query->byCategory($request->category_code);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $merchants = $query->get()->map(fn($m) => $this->formatMerchant($m));

        return response()->json(['status' => true, 'data' => $merchants]);
    }

    // ── GET /merchant/{id} ────────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $merchant = Merchant::active()
            ->with('category:id,code,name,color_hex')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => array_merge(
                $this->formatMerchant($merchant),
                ['input_config' => $merchant->input_config]
            ),
        ]);
    }

    // ── GET /merchant/{id}/products ───────────────────────────────────────────
    public function products(int $id): JsonResponse
    {
        $merchant = Merchant::active()->findOrFail($id);

        $products = $merchant->availableProducts()
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'code'         => $p->code,
                'name'         => $p->name,
                'description'  => $p->description,
                'validity'     => $p->validity,
                'selling_price'=> (float) $p->selling_price,
                'admin_fee'    => (float) $p->admin_fee,
                'total_price'  => (float) $p->total_price,
                'category_tag' => $p->category_tag,
            ]);

        return response()->json([
            'status' => true,
            'data'   => $products,
            'meta'   => [
                'merchant_id'   => $merchant->id,
                'merchant_name' => $merchant->name,
                'has_inquiry'   => $merchant->has_inquiry,
            ],
        ]);
    }

    // ── POST /merchant/inquiry ────────────────────────────────────────────────
    public function inquiry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'merchant_id'  => 'required|integer|exists:merchants,id',
            'input_value'  => 'required|string|min:5|max:30',
        ]);

        $merchant = Merchant::active()->findOrFail($validated['merchant_id']);

        if (!$merchant->has_inquiry) {
            return response()->json([
                'status'  => false,
                'message' => 'Merchant ini tidak memerlukan cek tagihan.',
            ], 422);
        }

        $result = $this->paymentService->inquiry(
            merchant:   $merchant,
            inputValue: $validated['input_value'],
        );

        if (!$result['success']) {
            return response()->json(['status' => false, 'message' => $result['message']], 422);
        }

        return response()->json(['status' => true, 'data' => $result['data']]);
    }

    // ── POST /merchant/payment ────────────────────────────────────────────────
    public function payment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'merchant_id'     => 'required|integer|exists:merchants,id',
            'product_id'      => 'required|integer|exists:merchant_products,id',
            'input_value'     => 'required|string|min:5|max:50',
            'idempotency_key' => 'required|string|size:36',
            'pin'             => 'required|string|digits:6',
        ]);

        $user     = $request->user();
        $merchant = Merchant::active()->findOrFail($validated['merchant_id']);
        $product  = MerchantProduct::where('merchant_id', $merchant->id)
            ->where('is_available', true)
            ->findOrFail($validated['product_id']);

        $wallet = $user->wallet;
        if (!$wallet || !$wallet->pin_set) {
            return response()->json(['status' => false, 'message' => 'PIN wallet belum diset.'], 422);
        }
        if (!$wallet->verifyPin($validated['pin'])) {
            return response()->json(['status' => false, 'message' => 'PIN wallet tidak valid.'], 422);
        }

        if ($wallet->balance < $product->total_price) {
            return response()->json([
                'status'  => false,
                'message' => 'Saldo tidak mencukupi.',
                'data'    => [
                    'balance'     => (float) $wallet->balance,
                    'total_price' => (float) $product->total_price,
                ],
            ], 422);
        }

        $result = $this->paymentService->processPayment(
            user:           $user,
            merchant:       $merchant,
            product:        $product,
            inputValue:     $validated['input_value'],
            idempotencyKey: $validated['idempotency_key'],
            wallet:         $wallet,
        );

        if (!$result['success']) {
            return response()->json(['status' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Pembayaran berhasil.',
            'data'    => $result['data'],
        ]);
    }

    // ── GET /merchant/transactions/history ────────────────────────────────────
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = MerchantTransaction::forUser($user->id)
            ->with(['merchant:id,name,code', 'product:id,name,code'])
            ->paginate(15);

        $mapped = $transactions->getCollection()->map(fn($t) => [
            'id'               => $t->id,
            'merchant_name'    => $t->merchant->name,
            'product_name'     => $t->product->name,
            'input_value'      => $t->input_value,
            'total_amount'     => (float) $t->total_amount,
            'status'           => $t->status,
            'status_label'     => $t->status_label,
            'status_color'     => $t->status_color,
            'provider_reference' => $t->provider_reference,
            'created_at'       => $t->created_at,
        ]);

        return response()->json([
            'status' => true,
            'data'   => $mapped,
            'meta'   => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }

    // ── GET /merchant/transactions/{id}/receipt ───────────────────────────────
    public function receipt(int $id, Request $request): JsonResponse
    {
        $transaction = MerchantTransaction::forUser($request->user()->id)
            ->with(['merchant:id,name,code,logo_url', 'product:id,name,code'])
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'               => $transaction->id,
                'merchant_name'    => $transaction->merchant->name,
                'merchant_logo'    => $transaction->merchant->logo_url,
                'product_name'     => $transaction->product->name,
                'input_value'      => $transaction->input_value,
                'total_amount'     => (float) $transaction->total_amount,
                'admin_fee'        => (float) $transaction->admin_fee,
                'status'           => $transaction->status,
                'status_label'     => $transaction->status_label,
                'status_color'     => $transaction->status_color,
                'provider_reference' => $transaction->provider_reference,
                'receipt_data'     => $transaction->receipt_data,
                'created_at'       => $transaction->created_at,
                'processed_at'     => $transaction->processed_at,
            ],
        ]);
    }

    // ── Helper: Format merchant response ──────────────────────────────────────
    private function formatMerchant(Merchant $merchant): array
    {
        // ✅ FIX: Gunakan config('app.url') + path publik
        // File logo ada di public/uploads/kypay/merchant-logos/
        // Bukan di storage/ — jadi tidak pakai Storage::url()
        $logoUrl = null;
        if ($merchant->logo_url) {
            $logoUrl = config('app.url') . '/uploads/kypay/merchant-logos/' . $merchant->logo_url;
        }

        return [
            'id'          => $merchant->id,
            'code'        => $merchant->code,
            'name'        => $merchant->name,
            'logo_url'    => $logoUrl,
            'has_inquiry' => $merchant->has_inquiry,
            'is_featured' => $merchant->is_featured,
            'category'    => $merchant->category ? [
                'id'        => $merchant->category->id,
                'code'      => $merchant->category->code,
                'name'      => $merchant->category->name,
                'color_hex' => $merchant->category->color_hex,
            ] : null,
            'input_config' => $merchant->input_config,
        ];
    }
}