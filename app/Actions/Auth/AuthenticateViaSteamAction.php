<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\ProvablyFair\GenerateSeedPairAction;
use App\Models\User;
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
            $user = User::create([
                'steam_id' => $steamId,
                'username' => $steamUser->getNickname(),
                'avatar_url' => $steamUser->getAvatar(),
                'last_active_at' => now(),
            ]);

            $this->generateSeedPair->execute($user);

            return $user;
        });
    }
}
