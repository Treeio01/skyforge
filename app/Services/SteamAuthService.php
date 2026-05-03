<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Auth\CreateUserFromSteamAction;
use App\Actions\Auth\FindUserBySteamIdAction;
use App\Actions\Auth\PullAttributionAction;
use App\Actions\Auth\UpdateUserFromSteamAction;
use App\Actions\ProvablyFair\GenerateSeedPairAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SteamAuthService
{
    public function __construct(
        private FindUserBySteamIdAction $findUser,
        private UpdateUserFromSteamAction $updateUser,
        private CreateUserFromSteamAction $createUser,
        private PullAttributionAction $pullAttribution,
        private GenerateSeedPairAction $generateSeedPair,
    ) {}

    public function authenticate(SocialiteUser $steamUser): User
    {
        return $this->authenticateFromPrimitives(
            steamId: $steamUser->getId(),
            username: (string) $steamUser->getNickname(),
            avatarUrl: (string) $steamUser->getAvatar(),
        );
    }

    /**
     * Bridge entrypoint: the auth domain has already verified the user
     * with Steam and packed the result into a signed token. Consumer
     * domain receives only primitives, no SocialiteUser available.
     */
    public function authenticateFromPrimitives(string $steamId, string $username, string $avatarUrl): User
    {
        $existing = $this->findUser->execute($steamId);

        if ($existing !== null) {
            return $this->updateUser->executeFromPrimitives($existing, $username, $avatarUrl);
        }

        return DB::transaction(function () use ($steamId, $username, $avatarUrl) {
            $user = $this->createUser->executeFromPrimitives(
                $steamId,
                $username,
                $avatarUrl,
                $this->pullAttribution->execute(),
            );

            $this->generateSeedPair->execute($user);

            return $user;
        });
    }
}
