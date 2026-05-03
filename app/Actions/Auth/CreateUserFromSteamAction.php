<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class CreateUserFromSteamAction
{
    /** @param array<string, mixed> $attribution */
    public function execute(string $steamId, SocialiteUser $steamUser, array $attribution): User
    {
        return $this->executeFromPrimitives(
            $steamId,
            (string) $steamUser->getNickname(),
            (string) $steamUser->getAvatar(),
            $attribution,
        );
    }

    /** @param array<string, mixed> $attribution */
    public function executeFromPrimitives(string $steamId, string $username, string $avatarUrl, array $attribution): User
    {
        return User::create([
            'steam_id' => $steamId,
            'username' => $username,
            'avatar_url' => $avatarUrl,
            'last_active_at' => now(),
            'utm_source' => $attribution['utm_source'] ?? null,
            'utm_medium' => $attribution['utm_medium'] ?? null,
            'utm_campaign' => $attribution['utm_campaign'] ?? null,
            'utm_content' => $attribution['utm_content'] ?? null,
            'utm_term' => $attribution['utm_term'] ?? null,
            'referrer' => $attribution['referrer'] ?? null,
            'registration_ip' => $attribution['ip'] ?? request()->ip(),
            'utm_mark_id' => $attribution['mark_id'] ?? null,
        ]);
    }
}
