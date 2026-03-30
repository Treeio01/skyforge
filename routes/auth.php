<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\SteamAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/auth/steam', [SteamAuthController::class, 'redirect'])->name('auth.steam');
    Route::get('/auth/steam/callback', [SteamAuthController::class, 'callback'])->name('auth.steam.callback');
});

Route::post('/auth/logout', [SteamAuthController::class, 'logout'])->name('logout')->middleware('auth');
