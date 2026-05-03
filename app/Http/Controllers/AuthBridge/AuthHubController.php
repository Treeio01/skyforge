<?php

declare(strict_types=1);

namespace App\Http\Controllers\AuthBridge;

use App\Actions\AuthBridge\IssueBridgeTokenAction;
use App\Http\Controllers\Controller;
use App\Services\AuthBridge\ConsumerDomainRegistry;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * Auth domain endpoints. The consumer domains redirect users here; this hub
 * runs the Steam OpenID round-trip and sends them back to the consumer's
 * /auth/consume endpoint with a one-shot signed token.
 */
class AuthHubController extends Controller
{
    private const RETURN_SESSION_KEY = 'auth_bridge.return_url';

    public function login(Request $request, ConsumerDomainRegistry $consumers): RedirectResponse
    {
        $return = (string) $request->query('return', '');

        if (! $consumers->isAllowedUrl($return)) {
            return redirect()->away($consumers->fallbackHomeUrl());
        }

        $request->session()->put(self::RETURN_SESSION_KEY, $return);

        return Socialite::driver('steam')->redirect();
    }

    public function callback(
        Request $request,
        ConsumerDomainRegistry $consumers,
        IssueBridgeTokenAction $issueToken,
    ): RedirectResponse {
        $return = (string) $request->session()->pull(self::RETURN_SESSION_KEY, '');

        if (! $consumers->isAllowedUrl($return)) {
            return redirect()->away($consumers->fallbackHomeUrl());
        }

        $consume = $consumers->consumeUrlFor($return);

        try {
            $steamUser = Socialite::driver('steam')->user();
        } catch (Exception) {
            return redirect()->away($consume.'?error=steam_unavailable&next='.urlencode($return));
        }

        $token = $issueToken->execute($steamUser);

        return redirect()->away($consume.'?token='.urlencode($token).'&next='.urlencode($return));
    }
}
