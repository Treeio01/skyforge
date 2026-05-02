<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DepositActionsController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FaqBulkController;
use App\Http\Controllers\Admin\FaqSortController;
use App\Http\Controllers\Admin\OnlineSettingsController;
use App\Http\Controllers\Admin\PromoCodeBulkController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\SkinBulkController;
use App\Http\Controllers\Admin\UserActionsController;
use App\Http\Controllers\Admin\WithdrawalActionsController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\LiveFeedController;
use App\Http\Controllers\ProvablyFairController;
use App\Http\Controllers\SkinController;
use App\Http\Controllers\UpgradeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Middleware\EnsureTradeUrlSet;
use Illuminate\Support\Facades\Route;
use MoonShine\Laravel\Http\Middleware\Authenticate;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| SKYFORGE routes. Auth is Steam-only (see auth.php).
|
*/

Route::get('/api/skins', [SkinController::class, 'index'])
    ->middleware('throttle:api')
    ->name('skins.index');
Route::get('/api/skins/search', [SkinController::class, 'search'])
    ->middleware('throttle:api')
    ->name('skins.search');
Route::get('/api/live-feed', [LiveFeedController::class, 'index'])->name('live-feed');

Route::get('/', [UpgradeController::class, 'index'])->name('home');
Route::get('/market', [SkinController::class, 'market'])->name('market');

Route::middleware('auth')->group(function () {
    Route::post('/market/buy', [SkinController::class, 'buy'])
        ->middleware('throttle:api')
        ->name('market.buy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'show'])->name('profile');
    Route::put('/profile/trade-url', [UserController::class, 'updateTradeUrl'])
        ->middleware('throttle:tradeUrl')
        ->name('profile.trade-url');
    Route::get('/profile/history', [UserController::class, 'history'])->name('profile.history');
    Route::post('/profile/sell-skins', [UserController::class, 'sellSkins'])
        ->middleware('throttle:sellSkins')
        ->name('profile.sell-skins');
    Route::get('/profile/deposits', [UserController::class, 'deposits'])->name('profile.deposits');
    Route::post('/profile/promo', [UserController::class, 'redeemPromo'])
        ->middleware('throttle:promo')
        ->name('profile.promo');

    Route::get('/deposit', [DepositController::class, 'create'])->name('deposit.create');
    Route::get('/deposit/config', [DepositController::class, 'config'])->name('deposit.config');
    Route::post('/deposit', [DepositController::class, 'store'])
        ->middleware('throttle:deposit')
        ->name('deposit.store');

    Route::post('/upgrade', [UpgradeController::class, 'store'])
        ->name('upgrade.store')
        ->middleware('throttle:upgrade');

    Route::post('/withdrawal', [WithdrawalController::class, 'store'])
        ->name('withdrawal.store')
        ->middleware([EnsureTradeUrlSet::class, 'throttle:withdraw']);

    Route::get('/provably-fair', [ProvablyFairController::class, 'index'])->name('provably-fair');
    Route::post('/provably-fair/client-seed', [ProvablyFairController::class, 'updateClientSeed'])->name('provably-fair.client-seed');
    Route::get('/provably-fair/verify/{upgrade}', [ProvablyFairController::class, 'verify'])->name('provably-fair.verify');
});

Route::post('/api/webhooks/payment', [DepositController::class, 'webhook'])->name('webhook.payment');

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.online.')->group(function () {
    Route::post('online-settings', [OnlineSettingsController::class, 'update'])->name('save');
    Route::post('online-settings/reset', [OnlineSettingsController::class, 'reset'])->name('reset');
});

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.site-settings.')->group(function () {
    Route::post('site-settings', [SiteSettingsController::class, 'update'])->name('save');
});

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.withdrawals.')->group(function () {
    Route::post('withdrawals/{withdrawal}/approve', [WithdrawalActionsController::class, 'approve'])->name('approve');
    Route::post('withdrawals/{withdrawal}/reject', [WithdrawalActionsController::class, 'reject'])->name('reject');
});

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.deposits.')->group(function () {
    Route::post('deposits/{deposit}/complete', [DepositActionsController::class, 'markCompleted'])->name('complete');
});

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.users.')->group(function () {
    Route::post('users/{user}/ban', [UserActionsController::class, 'ban'])->name('ban');
    Route::post('users/{user}/unban', [UserActionsController::class, 'unban'])->name('unban');
});

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.skins.')->group(function () {
    Route::post('skins/bulk-activate', [SkinBulkController::class, 'activate'])->name('bulk-activate');
    Route::post('skins/bulk-deactivate', [SkinBulkController::class, 'deactivate'])->name('bulk-deactivate');
});

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.promo-codes.')->group(function () {
    Route::post('promo-codes/bulk-activate', [PromoCodeBulkController::class, 'activate'])->name('bulk-activate');
    Route::post('promo-codes/bulk-deactivate', [PromoCodeBulkController::class, 'deactivate'])->name('bulk-deactivate');
});

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.faq.')->group(function () {
    Route::post('faq/bulk-activate', [FaqBulkController::class, 'activate'])->name('bulk-activate');
    Route::post('faq/bulk-deactivate', [FaqBulkController::class, 'deactivate'])->name('bulk-deactivate');
    Route::post('faq/sort', FaqSortController::class)->name('sort');
});

Route::middleware([Authenticate::class])->prefix('admin')->name('moonshine.export.')->group(function () {
    Route::get('export/transactions', [ExportController::class, 'transactions'])->name('transactions');
    Route::get('export/deposits', [ExportController::class, 'deposits'])->name('deposits');
    Route::get('export/withdrawals', [ExportController::class, 'withdrawals'])->name('withdrawals');
});
