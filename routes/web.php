<?php

declare(strict_types=1);

use App\Http\Controllers\DepositController;
use App\Http\Controllers\LiveFeedController;
use App\Http\Controllers\ProvablyFairController;
use App\Http\Controllers\SkinController;
use App\Http\Controllers\UpgradeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Middleware\EnsureTradeUrlSet;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| SKYFORGE routes. Auth is Steam-only (see auth.php).
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/api/skins', [SkinController::class, 'index'])->name('skins.index');
Route::get('/api/skins/search', [SkinController::class, 'search'])->name('skins.search');
Route::get('/api/live-feed', [LiveFeedController::class, 'index'])->name('live-feed');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'show'])->name('profile');
    Route::put('/profile/trade-url', [UserController::class, 'updateTradeUrl'])->name('profile.trade-url');
    Route::get('/profile/history', [UserController::class, 'history'])->name('profile.history');

    Route::get('/deposit', [DepositController::class, 'create'])->name('deposit.create');
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
