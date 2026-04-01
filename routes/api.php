<?php

use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\PaymentApprovalController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Public\LicenseController;
use App\Http\Controllers\Public\OrderController;
use App\Http\Controllers\Public\PaymentProofController;
use App\Http\Controllers\Public\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [RegisterController::class, 'register']);

    Route::post('payment/webhook', [PaymentController::class, 'webhook']);

    Route::post('payment/upload-proof', [PaymentProofController::class, 'upload']);

    Route::get('products', [\App\Http\Controllers\Admin\ProductController::class, 'index']);

    Route::get('orders/{order_number}/status', [
        OrderController::class,
        'status',
    ]);

    Route::post('/license/verify', [LicenseController::class, 'verify']);

    Route::prefix('admin')->group(function () {
        Route::post('register', [AuthController::class, 'register']);

        Route::post('login', [AuthController::class, 'login']);

        Route::post('logout', [AuthController::class, 'logout'])->middleware(
            'auth:sanctum',
        );

        Route::middleware('auth:sanctum')->group(function () {
            Route::put('/profile', [AdminProfileController::class, 'update']);

            Route::get('/payments/pending', [PaymentApprovalController::class, 'listPendingPayments']);
            Route::post('/payments/approve', [PaymentApprovalController::class, 'approve']);
            Route::post('/payments/reject', [PaymentApprovalController::class, 'reject']);

            Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index']);
            Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store']);
            Route::put('/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'update']);
            Route::delete('/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy']);
            Route::put('/products/{id}/set-default', [\App\Http\Controllers\Admin\ProductController::class, 'setDefault']);

            Route::get('/orders', [AdminOrderController::class, 'index']);
        });
    });
});
