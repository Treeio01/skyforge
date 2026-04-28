<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\OnlineSettingsController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\LiveFeedController;
use App\Http\Controllers\ProvablyFairController;
use App\Http\Controllers\SkinController;
use App\Http\Controllers\UpgradeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Middleware\EnsureTradeUrlSet;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| SKYFORGE routes. Auth is Steam-only (see auth.php).
|
*/

Route::get('/api/skins', [SkinController::class, 'index'])->name('skins.index');
Route::get('/api/skins/search', [SkinController::class, 'search'])->name('skins.search');
Route::get('/api/live-feed', [LiveFeedController::class, 'index'])->name('live-feed');

Route::get('/', [UpgradeController::class, 'index'])->name('home');
Route::get('/market', [SkinController::class, 'market'])->name('market');

Route::middleware('auth')->group(function () {
    Route::post('/market/buy', [SkinController::class, 'buy'])->name('market.buy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'show'])->name('profile');
    Route::put('/profile/trade-url', [UserController::class, 'updateTradeUrl'])->name('profile.trade-url');
    Route::get('/profile/history', [UserController::class, 'history'])->name('profile.history');
    Route::post('/profile/sell-skins', [UserController::class, 'sellSkins'])->name('profile.sell-skins');
    Route::get('/profile/deposits', [UserController::class, 'deposits'])->name('profile.deposits');
    Route::post('/profile/promo', [UserController::class, 'redeemPromo'])->name('profile.promo');

    Route::get('/deposit', [DepositController::class, 'create'])->name('deposit.create');
    Route::get('/deposit/config', [DepositController::class, 'config'])->name('deposit.config');
    Route::post('/deposit', [DepositController::class, 'store'])->name('deposit.store');

    Route::post('/upgrade', [UpgradeController::class, 'store'])
        ->name('upgrade.store')
        ->middleware('throttle:upgrade');

    Route::post('/withdrawal', [WithdrawalController::class, 'store'])
        ->name('withdrawal.store')
        ->middleware(EnsureTradeUrlSet::class);

    Route::get('/provably-fair', [ProvablyFairController::class, 'index'])->name('provably-fair');
    Route::post('/provably-fair/client-seed', [ProvablyFairController::class, 'updateClientSeed'])->name('provably-fair.client-seed');
    Route::get('/provably-fair/verify/{upgrade}', [ProvablyFairController::class, 'verify'])->name('provably-fair.verify');
});

Route::post('/api/webhooks/payment', [DepositController::class, 'webhook'])->name('webhook.payment');

Route::middleware(['auth'])->prefix('admin')->name('moonshine.online.')->group(function () {
    Route::post('online-settings', [OnlineSettingsController::class, 'update'])->name('save');
    Route::post('online-settings/reset', [OnlineSettingsController::class, 'reset'])->name('reset');
});
