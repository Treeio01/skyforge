<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\SteamAuthController;
use App\Http\Controllers\AuthBridge\AuthConsumeController;
use App\Http\Controllers\AuthBridge\AuthHubController;
use Illuminate\Support\Facades\Route;

/*
| Auth bridge routes are split by domain so a single Laravel app serves both
| roles. The auth domain (config/auth_bridge.php → auth_domain) hosts the
| Steam OpenID round-trip; every consumer domain hosts /auth/consume to
| receive the signed token.
*/

$authDomain = (string) config('auth_bridge.auth_domain');

Route::domain($authDomain)->middleware(['throttle:auth'])->group(function () {
    Route::get('/login', [AuthHubController::class, 'login'])->name('auth_bridge.login');
    Route::get('/steam/callback', [AuthHubController::class, 'callback'])->name('auth_bridge.callback');
});

Route::get('/auth/consume', AuthConsumeController::class)
    ->middleware(['throttle:auth'])
    ->name('auth_bridge.consume');

/*
| Legacy direct Steam auth on the consumer domain. Still active when
| AUTH_BRIDGE_ENABLED=false (local dev) or as a fallback. Production
| traffic uses the bridge instead and the Steam app realm should be
| moved to the auth domain.
*/
Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::get('/auth/steam', [SteamAuthController::class, 'redirect'])->name('auth.steam');
    Route::get('/auth/steam/callback', [SteamAuthController::class, 'callback'])->name('auth.steam.callback');
});

Route::post('/auth/logout', [SteamAuthController::class, 'logout'])->name('logout')->middleware('auth');
