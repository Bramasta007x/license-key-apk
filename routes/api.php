<?php

use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\ArtistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LandingPageConfigController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Public\OrderController;
use App\Http\Controllers\Public\RegisterController;
use App\Http\Controllers\Public\TicketController;


Route::prefix("v1")->group(function () {
    Route::get('landing-page', [LandingPageConfigController::class, 'getConfig']);

    Route::get('artists', [ArtistController::class, 'index']);

    Route::get("tickets", [TicketController::class, "index"]);

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
            Route::get("dashboard/summary", [
                DashboardController::class,
                "summary",
            ]);
            Route::get("dashboard/daily-sales", [
                DashboardController::class,
                "dailySales",
            ]);
            Route::get("participants/summary", [
                DashboardController::class,
                "participantsSummary",
            ]);

            Route::put("tickets/{id}", [TicketController::class, "update"]);

            Route::get("participants", [ParticipantController::class, "index"]);

            Route::delete('participants/{unique_id}', [ParticipantController::class, 'delete']);

            Route::post('artists', [ArtistController::class, 'store']);

            Route::put('artists/{id}', [ArtistController::class, 'update']);

            Route::delete('artists/{id}', [ArtistController::class, 'destroy']);

            Route::post('landing-page', [LandingPageConfigController::class, 'update']);

            Route::put('/profile', [AdminProfileController::class, 'update']);
        });
    });
});
