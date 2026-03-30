<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateViaSteamAction;
use App\Http\Controllers\Controller;
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

    public function callback(AuthenticateViaSteamAction $action): RedirectResponse
    {
        try {
            $steamUser = Socialite::driver('steam')->user();
        } catch (\Exception) {
            return redirect()->route('home')->with('error', 'Steam временно недоступен. Попробуйте позже.');
        }

        $user = $action->execute($steamUser);

        if ($user->is_banned) {
            return redirect()->route('home')->with('error', 'Ваш аккаунт заблокирован: '.$user->ban_reason);
        }

        Auth::login($user, remember: true);

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
