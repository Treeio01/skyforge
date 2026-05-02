<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\IssueSessionAction;
use App\Http\Controllers\Controller;
use App\Services\SteamAuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SteamAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('steam')->redirect();
    }

    public function callback(SteamAuthService $service, IssueSessionAction $issueSession): RedirectResponse
    {
        try {
            $steamUser = Socialite::driver('steam')->user();
        } catch (Exception) {
            return redirect()->route('home')->with('error', 'Steam временно недоступен. Попробуйте позже.');
        }

        $user = $service->authenticate($steamUser);

        if ($user->is_banned) {
            return redirect()->route('home')->with('error', 'Ваш аккаунт заблокирован: '.$user->ban_reason);
        }

        $issueSession->execute($user);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
