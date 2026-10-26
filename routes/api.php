<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::post('webhooks/{provider}', WebhookController::class)->where('provider', 'stripe');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('contract-cycles/{cycle}/payments', [PaymentController::class, 'store']);
        Route::get('payments/{payment}', [PaymentController::class, 'show']);
    });
});
