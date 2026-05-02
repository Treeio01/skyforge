<?php

declare(strict_types=1);

namespace App\Actions\Withdrawal;

use App\Enums\UserSkinStatus;
use App\Models\User;
use App\Models\UserSkin;

class LockAvailableUserSkinAction
{
    public function execute(User $user, int $userSkinId): UserSkin
    {
        return UserSkin::query()
            ->lockForUpdate()
            ->where('id', $userSkinId)
            ->where('user_id', $user->id)
            ->where('status', UserSkinStatus::Available)
            ->firstOrFail();
    }
}
