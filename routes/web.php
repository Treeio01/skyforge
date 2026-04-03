<?php

declare(strict_types=1);

use App\Http\Controllers\SkinController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'show'])->name('profile');
    Route::put('/profile/trade-url', [UserController::class, 'updateTradeUrl'])->name('profile.trade-url');
    Route::get('/profile/history', [UserController::class, 'history'])->name('profile.history');
});
