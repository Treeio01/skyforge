<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Если site_enabled = false — показываем maintenance-страницу всем,
 * кроме админских маршрутов (admin/...) и Steam-callback'ов.
 */
class EnsureSiteEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ((bool) Setting::get('site_enabled', true)) {
            return $next($request);
        }

        if ($this->isAllowedPath($request)) {
            return $next($request);
        }

        $message = (string) Setting::get('maintenance_message', '');

        return Inertia::render('Maintenance', [
            'message' => $message !== '' ? $message : 'Сайт временно на обслуживании. Скоро вернёмся.',
        ])->toResponse($request)->setStatusCode(503);
    }

    private function isAllowedPath(Request $request): bool
    {
        return $request->is('admin*')
            || $request->is('auth/*')
            || $request->is('login')
            || $request->is('logout')
            || $request->is('steam/*')
            || $request->is('api/webhooks/*');
    }
}
