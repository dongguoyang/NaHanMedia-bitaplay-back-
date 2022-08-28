<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\OccupationController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\ProviderFinanceController;
use App\Http\Controllers\Admin\UserFinanceController;
use App\Http\Controllers\Admin\ToolController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\HotWordController;
use App\Http\Controllers\Admin\WebFeedbackController;
use App\Http\Controllers\Admin\AvatarController;
use App\Http\Controllers\Admin\SearchWordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HelpController;

/**
 * 后台
 */

$domain = env('ADMIN_DOMAIN');
Route::domain($domain)->group(function () {

    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminController::class, 'login']);
    });

    Route::prefix('tool')->group(function () {
        Route::post('/upload-image', [ToolController::class, 'uploadImage']);
        Route::any('/ks_ads', [ToolController::class, 'kuaishouAds']);
    });

    Route::middleware('auth.admin')->group(function () {
        Route::prefix('dashboard')->group(function () {
            Route::get('/index', [DashboardController::class, 'index']);
            Route::get('/ks-ads', [DashboardController::class, 'kuaishouAds']);
        });
        Route::prefix('admin')->group(function () {
            Route::get('/info', [AdminController::class, 'info']);
        });
        Route::prefix('provider')->group(function () {
            Route::post('/list', [ProviderController::class, 'list']);
            Route::post('/edit-status', [ProviderController::class, 'editStatus']);
            Route::post('/info', [ProviderController::class, 'info']);
            Route::post('/edit-info-status', [ProviderController::class, 'editInfoStatus']);
        });
        Route::prefix('app')->group(function () {
            Route::post('/list', [AppController::class, 'list']);
            Route::post('/version', [AppController::class, 'version']);
            Route::post('/edit-version-status', [AppController::class, 'editVersionStatus']);
            Route::post('/edit-recommend', [AppController::class, 'editRecommend']);
            Route::post('/edit-download-count', [AppController::class, 'editDownloadCount']);
            Route::post('/download-record', [AppController::class, 'downloadRecord']);
        });
        Route::prefix('user')->group(function () {
            Route::post('/list', [UserController::class, 'list']);
            Route::post('/edit-status', [UserController::class, 'editStatus']);
        });
        Route::prefix('grade')->group(function () {
            Route::get('/list', [GradeController::class, 'list']);
            Route::post('/save', [GradeController::class, 'save']);
        });
        Route::prefix('industry')->group(function () {
            Route::post('/list', [IndustryController::class, 'list']);
            Route::get('/all', [IndustryController::class, 'all']);
            Route::post('/save', [IndustryController::class, 'save']);
        });
        Route::prefix('occupation')->group(function () {
            Route::post('/list', [OccupationController::class, 'list']);
            Route::post('/save', [OccupationController::class, 'save']);
        });
        Route::prefix('system')->group(function () {
            Route::post('/search', [SystemController::class, 'search']);
            Route::post('/save', [SystemController::class, 'save']);
            Route::get('/agreement', [SystemController::class, 'agreement']);
        });

        Route::prefix('provider-finance')->group(function () {
            Route::post('/recharge-balance', [ProviderFinanceController::class, 'rechargeBalance']);
            Route::post('/withdraw', [ProviderFinanceController::class, 'withdraw']);
            Route::post('/do-withdraw', [ProviderFinanceController::class, 'doWithdraw']);
            Route::post('/recharge-login', [ProviderFinanceController::class, 'rechargeLogin']);
            Route::post('/recharge-fuel', [ProviderFinanceController::class, 'rechargeFuel']);
            Route::post('/recharge-reward', [ProviderFinanceController::class, 'rechargeReward']);
        });

        Route::prefix('user-finance')->group(function () {
            Route::post('/withdraw', [UserFinanceController::class, 'withdraw']);
            Route::post('/do-withdraw', [UserFinanceController::class, 'doWithdraw']);
            Route::post('/download-reward', [UserFinanceController::class, 'downloadReward']);
            Route::post('/recommend-reward', [UserFinanceController::class, 'recommendReward']);
        });

        Route::prefix('feedback')->group(function () {
            Route::post('/list', [FeedbackController::class, 'list']);
            Route::post('/handel', [FeedbackController::class, 'handel']);
        });

        Route::prefix('web-feedback')->group(function () {
            Route::post('/list', [WebFeedbackController::class, 'list']);
            Route::post('/handel', [WebFeedbackController::class, 'handel']);
        });

        Route::prefix('hot-word')->group(function () {
            Route::post('/list', [HotWordController::class, 'list']);
            Route::post('/save', [HotWordController::class, 'save']);
            Route::post('/del', [HotWordController::class, 'del']);
        });
        Route::prefix('search-word')->group(function () {
            Route::post('/list', [SearchWordController::class, 'list']);
        });

        Route::prefix('avatar')->group(function () {
            Route::post('/list', [AvatarController::class, 'list']);
            Route::post('/save', [AvatarController::class, 'save']);
            Route::post('/del', [AvatarController::class, 'del']);
        });

        Route::prefix('category')->group(function () {
            Route::post('/list', [CategoryController::class, 'list']);
            Route::post('/add', [CategoryController::class, 'add']);
            Route::get('/tree', [CategoryController::class, 'tree']);
            Route::get('/cascader-tree', [CategoryController::class, 'cascaderTree']);
        });

        Route::prefix('help')->group(function () {
            Route::post('/list', [HelpController::class, 'list']);
            Route::post('/handle', [HelpController::class, 'handle']);
        });

        Route::prefix('appVersion')->group(function () {
            Route::post('/list', [\App\Http\Controllers\Admin\AppVersionController::class, 'list']);
            Route::post('/save', [\App\Http\Controllers\Admin\AppVersionController::class, 'save']);
            Route::post('/edit-status', [\App\Http\Controllers\Admin\AppVersionController::class, 'editStatus']);
        });
    });
});

