<?php

declare(strict_types=1);

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
