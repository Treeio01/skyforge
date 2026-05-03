<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class UpdateUserFromSteamAction
{
    public function execute(User $user, SocialiteUser $steamUser): User
    {
        return $this->executeFromPrimitives(
            $user,
            (string) $steamUser->getNickname(),
            (string) $steamUser->getAvatar(),
        );
    }

    public function executeFromPrimitives(User $user, string $username, string $avatarUrl): User
    {
        $user->update([
            'username' => $username,
            'avatar_url' => $avatarUrl,
            'last_active_at' => now(),
        ]);

        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }
}
