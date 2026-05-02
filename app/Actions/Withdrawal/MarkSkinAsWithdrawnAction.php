<?php

declare(strict_types=1);

namespace App\Actions\Withdrawal;

use App\Enums\UserSkinStatus;
use App\Models\UserSkin;

class MarkSkinAsWithdrawnAction
{
    public function execute(UserSkin $userSkin): void
    {
        $userSkin->update(['status' => UserSkinStatus::Withdrawn]);
    }
}
