<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\ProvablyFair\GenerateSeedPairAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthenticateViaSteamAction
{
    public function __construct(
        private GenerateSeedPairAction $generateSeedPair,
    ) {}

    public function execute(SocialiteUser $steamUser): User
    {
        $steamId = $steamUser->getId();

        $user = User::withTrashed()->where('steam_id', $steamId)->first();

        if ($user) {
            $user->update([
                'username' => $steamUser->getNickname(),
                'avatar_url' => $steamUser->getAvatar(),
                'last_active_at' => now(),
            ]);

            if ($user->trashed()) {
                $user->restore();
            }

            return $user;
        }

        return DB::transaction(function () use ($steamId, $steamUser) {
            $attribution = $this->pullAttribution();

            $user = User::create([
                'steam_id' => $steamId,
                'username' => $steamUser->getNickname(),
                'avatar_url' => $steamUser->getAvatar(),
                'last_active_at' => now(),
                'utm_source' => $attribution['utm_source'] ?? null,
                'utm_medium' => $attribution['utm_medium'] ?? null,
                'utm_campaign' => $attribution['utm_campaign'] ?? null,
                'utm_content' => $attribution['utm_content'] ?? null,
                'utm_term' => $attribution['utm_term'] ?? null,
                'referrer' => $attribution['referrer'] ?? null,
                'registration_ip' => $attribution['ip'] ?? request()->ip(),
            ]);

            $this->generateSeedPair->execute($user);

            return $user;
        });
    }

    /**
     * Pop attribution data from session (set by CaptureUtm middleware on landing).
     *
     * @return array<string, string>
     */
    private function pullAttribution(): array
    {
        $request = app(Request::class);

        if (! $request->hasSession()) {
            return [];
        }

        $data = $request->session()->pull('attribution', []);

        return is_array($data) ? $data : [];
    }
}
