<?php

declare(strict_types=1);

use App\Http\Middleware\CaptureUtm;
use App\Http\Middleware\EnsureSiteEnabled;
use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureSiteEnabled::class,
            EnsureUserIsNotBanned::class,
            CaptureUtm::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ((bool) config('auth_bridge.enabled')) {
                $authDomain = (string) config('auth_bridge.auth_domain');

                return 'https://'.$authDomain.'/login?return='.urlencode($request->fullUrl());
            }

            return route('auth.steam');
        });
        $middleware->redirectUsersTo(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            if (in_array($status, [403, 404, 419, 500, 503]) && ! app()->environment('local')) {
                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
