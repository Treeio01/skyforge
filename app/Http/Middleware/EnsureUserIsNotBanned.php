<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->is_banned || $request->is('admin*')) {
            return $next($request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Ваш аккаунт заблокирован.',
                'reason' => $user->ban_reason,
            ], 403);
        }

        $message = $user->ban_reason
            ? 'Ваш аккаунт заблокирован: '.$user->ban_reason
            : 'Ваш аккаунт заблокирован.';

        return redirect()
            ->route('home')
            ->with('error', $message);
    }
}
