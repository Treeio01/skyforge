<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class UpdateUserFromSteamAction
{
    public function execute(User $user, SocialiteUser $steamUser): User
    {
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
}
