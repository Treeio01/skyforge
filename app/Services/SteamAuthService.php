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
        $existing = $this->findUser->execute($steamUser->getId());

        if ($existing !== null) {
            return $this->updateUser->execute($existing, $steamUser);
        }

        return DB::transaction(function () use ($steamUser) {
            $user = $this->createUser->execute(
                $steamUser->getId(),
                $steamUser,
                $this->pullAttribution->execute(),
            );

            $this->generateSeedPair->execute($user);

            return $user;
        });
    }
}
