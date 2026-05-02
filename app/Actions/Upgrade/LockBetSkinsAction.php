<?php

declare(strict_types=1);

namespace App\Actions\Upgrade;

use App\Enums\UserSkinStatus;
use App\Models\User;
use App\Models\UserSkin;
use DomainException;
use Illuminate\Database\Eloquent\Collection;

class LockBetSkinsAction
{
    /**
     * @param  array<int, int>  $userSkinIds
     * @return Collection<int, UserSkin>
     */
    public function execute(User $user, array $userSkinIds): Collection
    {
        $skins = UserSkin::query()
            ->lockForUpdate()
            ->whereIn('id', $userSkinIds)
            ->where('user_id', $user->id)
            ->where('status', UserSkinStatus::Available)
            ->with('skin')
            ->get();

        if ($skins->count() !== count($userSkinIds)) {
            throw new DomainException('Некоторые скины недоступны для апгрейда.');
        }

        return $skins;
    }
}
