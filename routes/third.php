<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Third\UserController;
use App\Http\Controllers\Third\ProviderController;
use App\Http\Controllers\Third\WebhookController;

/**
 * 用户
 */

$domain = env('THIRD_DOMAIN');
Route::domain($domain)->group(function () {
    Route::prefix('user')->group(function () {
        Route::any('/info', [UserController::class, 'info']);
        Route::get('/web-login', [UserController::class, 'webLogin']);
    });
    Route::prefix('provider')->group(function () {
        Route::post('/to-chain', [ProviderController::class, 'toChain']);
    });

    Route::prefix('webhook')->group(function () {
        Route::any('/alipay', [WebhookController::class, 'alipay']);
    });
});
