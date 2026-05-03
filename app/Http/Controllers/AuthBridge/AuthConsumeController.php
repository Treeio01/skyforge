<?php

declare(strict_types=1);

namespace App\Http\Controllers\AuthBridge;

use App\Actions\Auth\IssueSessionAction;
use App\Exceptions\AuthBridgeTokenException;
use App\Http\Controllers\Controller;
use App\Services\AuthBridge\AuthBridgeTokenService;
use App\Services\AuthBridge\ConsumerDomainRegistry;
use App\Services\SteamAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Consumer domain endpoint. Receives the signed token from the auth domain,
 * validates it, materialises (or updates) the local user record, and starts
 * a session — same end state as the legacy /auth/steam/callback used to give.
 */
class AuthConsumeController extends Controller
{
    public function __invoke(
        Request $request,
        AuthBridgeTokenService $tokens,
        SteamAuthService $steamAuth,
        IssueSessionAction $issueSession,
        ConsumerDomainRegistry $consumers,
    ): RedirectResponse {
        if ($request->query('error') === 'steam_unavailable') {
            return redirect()->route('home')->with('error', 'Steam временно недоступен. Попробуйте позже.');
        }

        $token = (string) $request->query('token', '');

        if ($token === '') {
            return redirect()->route('home')->with('error', 'Невалидная ссылка авторизации.');
        }

        try {
            $payload = $tokens->consume($token);
        } catch (AuthBridgeTokenException) {
            return redirect()->route('home')->with('error', 'Сессия авторизации истекла. Попробуйте ещё раз.');
        }

        $user = $steamAuth->authenticateFromPrimitives(
            steamId: (string) ($payload['steam_id'] ?? ''),
            username: (string) ($payload['username'] ?? ''),
            avatarUrl: (string) ($payload['avatar_url'] ?? ''),
        );

        if ($user->is_banned) {
            return redirect()->route('home')->with('error', 'Ваш аккаунт заблокирован: '.$user->ban_reason);
        }

        $issueSession->execute($user);

        $next = (string) $request->query('next', '');

        return $consumers->isSameHostAllowedUrl($next, $request->getHost())
            ? redirect()->away($next)
            : redirect()->route('home');
    }
}
