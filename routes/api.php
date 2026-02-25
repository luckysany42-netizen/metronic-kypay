<?php

use App\Http\Controllers\Api\AuthController;

Route::post('/login',           [AuthController::class, 'login']);
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/verify_token',    [AuthController::class, 'verifyToken']);
Route::post('/logout',          [AuthController::class, 'logout']);
Route::post('/profile/update',  [AuthController::class, 'updateProfile']);
Route::post('/profile/avatar',  [AuthController::class, 'uploadAvatar']);
Route::post('/change-password', [AuthController::class, 'changePassword']); // ← BARU

Route::post('/admin/login',    [AuthController::class, 'adminLogin']);
Route::post('/admin/register', [AuthController::class, 'adminRegister']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);