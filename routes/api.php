<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TopUpController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\Admin\AdminTopUpController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;
use App\Http\Controllers\Api\Admin\AdminUserController;

// ================================================================
// PUBLIC ROUTES — tidak perlu token
// ================================================================

Route::post('/login',           [AuthController::class, 'login']);
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/verify_token',    [AuthController::class, 'verifyToken']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

Route::post('/admin/login',    [AuthController::class, 'adminLogin']);
Route::post('/admin/register', [AuthController::class, 'adminRegister']);

Route::post('/wallet/set-initial-pin', [WalletController::class, 'setInitialPin']);

// ================================================================
// USER ROUTES — perlu token (Authorization: Token {api_token})
// ================================================================

Route::middleware(['auth.token'])->group(function () {

    // --- Auth & Profile ---
    Route::post('/logout',          [AuthController::class, 'logout']);
    Route::post('/profile/update',  [AuthController::class, 'updateProfile']);
    Route::post('/profile/avatar',  [AuthController::class, 'uploadAvatar']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // --- KyPay: Wallet ---
    Route::prefix('wallet')->group(function () {
        Route::get('/',                     [WalletController::class, 'show']);
        Route::get('/transactions',         [WalletController::class, 'transactions']);
        Route::post('/set-pin',             [WalletController::class, 'setPin']);
        Route::post('/verify-pin',          [WalletController::class, 'verifyPin']);
        Route::get('/find/{wallet_number}', [WalletController::class, 'findByNumber']);
    });

    // --- KyPay: Top Up ---
    Route::prefix('topup')->group(function () {
        Route::get('/',                [TopUpController::class, 'index']);
        Route::post('/',               [TopUpController::class, 'store']);
        Route::get('/payment-methods', [TopUpController::class, 'paymentMethods']);
        Route::get('/{id}',            [TopUpController::class, 'show']);
    });

    // --- KyPay: Transfer ---
    Route::prefix('transfer')->group(function () {
        Route::get('/',     [TransferController::class, 'index']);
        Route::post('/',    [TransferController::class, 'store']);
        Route::get('/{id}', [TransferController::class, 'show']);
    });

    // --- Account ---
    Route::post('/account/delete', [AuthController::class, 'deleteAccount']);

    // --- KyPay: Payment (Bayar & Beli) ---
    Route::prefix('payment')->group(function () {
        Route::get('/products', [PaymentController::class, 'products']);
        Route::post('/',        [PaymentController::class, 'store']);
    });

});

// ================================================================
// ADMIN ROUTES — perlu token + role admin
// ================================================================

Route::middleware(['auth.token', 'role:admin'])->prefix('admin')->group(function () {

    // --- KyPay Dashboard ---
    Route::get('/payment/dashboard', [AdminPaymentController::class, 'dashboard']);

    // --- Manage Top Up Requests ---
    Route::prefix('topup')->group(function () {
        Route::get('/',              [AdminTopUpController::class, 'index']);
        Route::get('/{id}',          [AdminTopUpController::class, 'show']);
        Route::post('/{id}/approve', [AdminTopUpController::class, 'approve']);
        Route::post('/{id}/reject',  [AdminTopUpController::class, 'reject']);
    });

    // --- Manage Wallets ---
    Route::prefix('wallets')->group(function () {
        Route::get('/',              [AdminPaymentController::class, 'wallets']);
        Route::patch('/{id}/status', [AdminPaymentController::class, 'updateWalletStatus']);
        Route::patch('/{id}/limit',  [AdminPaymentController::class, 'updateWalletLimit']);
    });

    // --- Manage Payment Methods ---
    Route::prefix('payment-methods')->group(function () {
        Route::get('/',        [AdminPaymentController::class, 'paymentMethods']);
        Route::post('/',       [AdminPaymentController::class, 'storePaymentMethod']);
        Route::put('/{id}',    [AdminPaymentController::class, 'updatePaymentMethod']);
        Route::delete('/{id}', [AdminPaymentController::class, 'destroyPaymentMethod']);
    });

    // --- All Transactions ---
    Route::get('/transactions', [AdminPaymentController::class, 'transactions']);

    // ✅ --- Manajemen User ---
    Route::prefix('users')->group(function () {
        Route::get('/',              [AdminUserController::class, 'index']);
        Route::post('/{id}/suspend',   [AdminUserController::class, 'suspend']);
        Route::post('/{id}/unsuspend', [AdminUserController::class, 'unsuspend']);
    });

});