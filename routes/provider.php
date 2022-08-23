<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Provider\ProviderController;
use App\Http\Controllers\Provider\ToolController;
use App\Http\Controllers\Provider\AppController;
use App\Http\Controllers\Provider\WalletController;
use App\Http\Controllers\Provider\DashboardController;
use App\Http\Controllers\Provider\WebhookController;
use App\Http\Controllers\Third\UserController;
use App\Http\Controllers\Provider\HelpController;

/**
 * 服务商
 */

$domain = env('PROVIDER_DOMAIN');
Route::domain($domain)->group(function () {

    Route::prefix('third')->group(function () {
        Route::post('/web-login', [UserController::class, 'webLogin']);
    });

    Route::prefix('provider')->group(function () {
        Route::post('/register', [ProviderController::class, 'register']);
        Route::post('/login', [ProviderController::class, 'login']);
        Route::get('/web-login-first', [ProviderController::class, 'webLoginFirst']);
        Route::any('/web-login-second', [ProviderController::class, 'webLoginSecond']);
        Route::post('/web-login-third', [ProviderController::class, 'webLoginThird']);
    });

    Route::prefix('tool')->group(function () {
        Route::post('/send-sms-verify-code', [ToolController::class, 'sendSmsVerifyCode']);
        Route::post('/send-email-verify-code', [ToolController::class, 'sendEmailVerifyCode']);
        Route::post('/upload-image', [ToolController::class, 'uploadImage']);
        Route::get('/system', [ToolController::class, 'system']);
        Route::get('/area', [ToolController::class, 'area']);
        Route::get('/industry', [ToolController::class, 'industry']);
    });

    Route::prefix('webhook')->group(function () {
        Route::any('/alipay', [WebhookController::class, 'alipay']);
    });


    Route::middleware('auth.provider')->group(function () {
        Route::prefix('dashboard')->group(function () {
            Route::get('/index', [DashboardController::class, 'index']);
        });

        Route::prefix('provider')->group(function () {
            Route::get('/info', [ProviderController::class, 'info']);
            Route::post('/bind-email', [ProviderController::class, 'bindEmail']);
            Route::post('/edit-info', [ProviderController::class, 'editInfo']);
            Route::post('/save-trans-pwd', [ProviderController::class, 'saveTransPwd']);
            Route::post('/show-trans-pwd', [ProviderController::class, 'showTransPwd']);
        });

        Route::prefix('tool')->group(function () {
            Route::get('/app-category', [ToolController::class, 'appCategory']);
            Route::get('/app-grade', [ToolController::class, 'appGrade']);
            Route::get('/android-shop', [ToolController::class, 'androidShop']);
            Route::get('/price', [ToolController::class, 'price']);
        });

        Route::prefix('app')->group(function () {
            Route::post('/list', [AppController::class, 'list']);
            Route::post('/create', [AppController::class, 'create']);
            Route::post('/detail', [AppController::class, 'detail']);
            Route::post('/app-detail', [AppController::class, 'appDetail']);
            Route::post('/version-list', [AppController::class, 'versionList']);
            Route::post('/save-version', [AppController::class, 'saveVersion']);
            Route::post('/version-detail', [AppController::class, 'versionDetail']);
            Route::post('/edit-status', [AppController::class, 'editStatus']);
            Route::post('/recharge-download-reward', [AppController::class, 'rechargeDownloadReward']);
            Route::post('/edit-download-reward', [AppController::class, 'editDownloadReward']);
            Route::post('/recharge-third-login', [AppController::class, 'rechargeThirdLogin']);
            Route::post('/recharge-fuel', [AppController::class, 'rechargeFuel']);
            Route::post('/third-login-statistics', [AppController::class, 'thirdLoginStatistics']);
            Route::post('/recharge-third-login-record', [AppController::class, 'rechargeThirdLoginRecord']);
            Route::post('/consume-third-login-record', [AppController::class, 'consumeThirdLoginRecord']);
            Route::post('/fuel-statistics', [AppController::class, 'fuelStatistics']);
            Route::post('/recharge-fuel-record', [AppController::class, 'rechargeFuelRecord']);
            Route::post('/consume-fuel-record', [AppController::class, 'consumeFuelRecord']);
            Route::post('/download-statistics', [AppController::class, 'downloadStatistics']);
            Route::post('/recharge-download-record', [AppController::class, 'rechargeDownloadRecord']);
            Route::post('/consume-download-record', [AppController::class, 'consumeDownloadRecord']);
            Route::post('/recommend', [AppController::class, 'recommend']);
            Route::post('/remind', [AppController::class, 'remind']);
            Route::post('/edit-download-reward-status', [AppController::class, 'editDownloadRewardStatus']);
        });
        Route::prefix('wallet')->group(function () {
            Route::get('/index', [WalletController::class, 'index']);
            Route::post('/recharge', [WalletController::class, 'recharge']);
            Route::post('/query-recharge', [WalletController::class, 'queryRecharge']);
            Route::post('/save-withdraw-account', [WalletController::class, 'saveWithdrawAccount']);
            Route::get('/withdraw-account', [WalletController::class, 'withdrawAccount']);
            Route::post('/withdraw', [WalletController::class, 'withdraw']);
            Route::post('/withdraw-record', [WalletController::class, 'withdrawRecord']);
            Route::post('/recharge-record', [WalletController::class, 'rechargeRecord']);
        });


        Route::prefix('help')->group(function () {
            Route::post('/help', [HelpController::class, 'help']);
        });
    });
});
