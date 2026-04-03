<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTradeUrlSet
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->trade_url) {
            return redirect()->route('profile')->with('error', 'Укажите Trade URL для вывода скинов.');
        }

        return $next($request);
    }
}
