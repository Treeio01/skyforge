<?php

declare(strict_types=1);

namespace App\Actions\AuthBridge;

use App\Services\AuthBridge\AuthBridgeTokenService;
use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Pack a Steam-authenticated user into a one-shot signed bridge token.
 * Centralised so the claim shape stays in lock-step with consume-time decoding.
 */
class IssueBridgeTokenAction
{
    public function __construct(private AuthBridgeTokenService $tokens)
    {
        //
    }

    public function execute(SocialiteUser $steamUser): string
    {
        return $this->tokens->issue([
            'steam_id' => (string) $steamUser->getId(),
            'username' => (string) $steamUser->getNickname(),
            'avatar_url' => (string) $steamUser->getAvatar(),
        ]);
    }
}
