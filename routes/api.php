<?php

use App\Http\Controllers\Admin\AdminProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Public\OrderController;
use App\Http\Controllers\Public\RegisterController;

Route::prefix("v1")->group(function () {
    Route::post("register", [RegisterController::class, "register"]);

    Route::post("payment/webhook", [PaymentController::class, "webhook"]);

    Route::get("orders/{order_number}/status", [
        OrderController::class,
        "status",
    ]);

    Route::prefix("admin")->group(function () {
        Route::post("register", [AuthController::class, "register"]);

        Route::post("login", [AuthController::class, "login"]);

        Route::post("logout", [AuthController::class, "logout"])->middleware(
            "auth:sanctum",
        );

        Route::middleware("auth:sanctum")->group(function () {
            Route::put('/profile', [AdminProfileController::class, 'update']);
        });
    });
});
