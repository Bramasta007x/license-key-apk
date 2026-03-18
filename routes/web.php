<?php

// use App\Http\Controllers\Admin\AuthController;
// use App\Http\Controllers\Admin\DashboardController;
// use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Public\{TicketController, RegisterController};
// use App\Http\Controllers\Payment\PaymentController;

// Route::prefix("v1")->group(function () {
//     Route::get("tickets", [TicketController::class, "index"]);
//     Route::post("register", [RegisterController::class, "store"]);

//     Route::post("payment/webhook", [PaymentController::class, "webhook"]);
//     Route::get("orders/{order_number}/status", [
//         PaymentController::class,
//         "status",
//     ]);

//     Route::prefix("admin")->group(function () {
//         Route::post("register", [AuthController::class, "register"]);
//         Route::post("login", [AuthController::class, "login"]);
//         Route::post("logout", [AuthController::class, "logout"])->middleware(
//             "auth:sanctum",
//         );

//         Route::middleware("auth:sanctum")->group(function () {
//             Route::get("dashboard/summary", [
//                 DashboardController::class,
//                 "summary",
//             ]);
//             Route::get("dashboard/daily-sales", [
//                 DashboardController::class,
//                 "dailySales",
//             ]);
//             Route::get("participants/summary", [
//                 DashboardController::class,
//                 "participantsSummary",
//             ]);
//         });
//     });
// });
