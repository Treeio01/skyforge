<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

class FindUserBySteamIdAction
{
    public function execute(string $steamId): ?User
    {
        return User::query()->withTrashed()->where('steam_id', $steamId)->first();
    }
}
