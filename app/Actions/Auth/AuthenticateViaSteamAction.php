<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\ProvablyFairSeed;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthenticateViaSteamAction
{
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

        $user = User::create([
            'steam_id' => $steamId,
            'username' => $steamUser->getNickname(),
            'avatar_url' => $steamUser->getAvatar(),
            'last_active_at' => now(),
        ]);

        $this->generateSeedPair($user);

        return $user;
    }

    private function generateSeedPair(User $user): void
    {
        $serverSeed = Str::random(64);

        ProvablyFairSeed::create([
            'user_id' => $user->id,
            'client_seed' => Str::random(32),
            'server_seed' => hash('sha256', $serverSeed),
            'server_seed_hash' => hash('sha256', $serverSeed),
            'nonce' => 0,
            'is_active' => true,
        ]);
    }
}
