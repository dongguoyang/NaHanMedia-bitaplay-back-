<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ToolController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\WebFeedbackController;
use App\Http\Controllers\Api\UsersAddressBookController;

/**
 * 用户
 */

$domain = env('API_DOMAIN');
Route::domain($domain)->group(function () {
    Route::prefix('test')->group(function () {
        Route::post('/encrypt', [TestController::class, 'encrypt']);
        Route::post('/decrypt', [TestController::class, 'decrypt']);
    });

    Route::prefix('tool')->group(function () {
        Route::post('/send-sms-verify-code', [ToolController::class, 'sendSmsVerifyCode']);
        Route::get('/sts', [ToolController::class, 'sts']);
        Route::get('/industry', [ToolController::class, 'industry']);
        Route::post('/occupation', [ToolController::class, 'occupation']);
        Route::post('/send-email-verify-code', [ToolController::class, 'sendEmailVerifyCode']);
        Route::get('/area', [ToolController::class, 'area']);
        Route::get('/app-category', [ToolController::class, 'appCategory']);
        Route::get('/system', [ToolController::class, 'system']);
        Route::get('/agreement', [ToolController::class, 'agreement']);
        Route::get('/hot-word', [ToolController::class, 'hotWord']);
        Route::post('/avatar', [ToolController::class, 'avatar']);
        Route::get('/grap', [ToolController::class, 'grap']);
        Route::get('/update-download', [ToolController::class, 'updateDownload']);
        Route::any('/ks_ads', [ToolController::class, 'kuaishouAds']);
    });

    Route::prefix('app')->group(function () {
        Route::post('/list', [AppController::class, 'list']);
        Route::post('/detail', [AppController::class, 'detail']);
        Route::post('/getAppVersion', [AppController::class, 'getAppVersion']);
    });

    Route::prefix('web-feedback')->group(function () {
        Route::post('/feedback', [WebFeedbackController::class, 'feedback']);
    });


    Route::prefix('user')->group(function () {
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/one-click-login', [UserController::class, 'oneClickLogin']);
        Route::post('/id-card-login', [UserController::class, 'idCardLogin']);
    });

    Route::middleware('auth.api')->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/basic-info', [UserController::class, 'basicInfo']);
            Route::post('/edit-basic-info', [UserController::class, 'editBasicInfo']);
            Route::post('/bind-email', [UserController::class, 'bindEmail']);
            Route::get('/other-info', [UserController::class, 'otherInfo']);
            Route::post('/edit-other-info', [UserController::class, 'editOtherInfo']);
            Route::post('/third-auth', [UserController::class, 'thirdAuth']);
            Route::post('/web-auth', [UserController::class, 'webAuth']);
            Route::post('/upload-id-card', [UserController::class, 'uploadIdCard']);
            Route::get('/edit-info-status', [UserController::class, 'editInfoStatus']);
            Route::post('/edit-ad-status', [UserController::class, 'editAdStatus']);
            Route::post('/edit-trans-password', [UserController::class, 'editTransPassword']);
            Route::post('/inviter-records', [UserController::class, 'inviterRecords']);
            Route::post('/grant-records', [UserController::class, 'grantRecord']);
            Route::get('/to-chain', [UserController::class, 'toChain']);
            Route::get('/create-did', [UserController::class, 'createDid']);
            Route::post('/other-info-to-chain', [UserController::class, 'otherInfoToChain']);
            Route::post('/feedback', [UserController::class, 'feedback']);
            Route::get('/cancel', [UserController::class, 'cancel']);
            Route::post('/edit-tel', [UserController::class, 'editTel']);
            Route::get('/persona', [UserController::class, 'persona']);
            Route::get('/open-ad-status-info', [UserController::class, 'openAdStatusInfo']);
        });

        Route::prefix('wallet')->group(function () {
            Route::get('/info', [WalletController::class, 'info']);
            Route::post('/save-withdraw-account', [WalletController::class, 'saveWithdrawAccount']);
            Route::get('/withdraw-account', [WalletController::class, 'withdrawAccount']);
            Route::post('/withdraw', [WalletController::class, 'withdraw']);
            Route::post('/records', [WalletController::class, 'records']);
        });

        Route::prefix('app')->group(function () {
            Route::post('/collect', [AppController::class, 'collect']);
            Route::post('/download', [AppController::class, 'download']);
        });

        Route::middleware('auth.api')->group(function () {
            Route::prefix('usersAddressBook')->group(function () {
                Route::post('/uploadAddressBook', [UsersAddressBookController::class, 'uploadAddressBook']);
                Route::post('/incomingPhone', [UsersAddressBookController::class, 'incomingPhone']);
                Route::post('/saveIncomingLog', [UsersAddressBookController::class, 'saveIncomingLog']);
                Route::post('/incomingLog', [UsersAddressBookController::class, 'incomingLog']);
                Route::post('/updateIncomingLog', [UsersAddressBookController::class, 'updateIncomingLog']);
            });
        });
    });
});
