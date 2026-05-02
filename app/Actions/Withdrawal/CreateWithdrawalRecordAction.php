<?php

declare(strict_types=1);

namespace App\Actions\Withdrawal;

use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\UserSkin;
use App\Models\Withdrawal;

class CreateWithdrawalRecordAction
{
    public function execute(User $user, UserSkin $userSkin): Withdrawal
    {
        return Withdrawal::create([
            'user_id' => $user->id,
            'user_skin_id' => $userSkin->id,
            'skin_id' => $userSkin->skin_id,
            'amount' => $userSkin->price_at_acquisition,
            'status' => WithdrawalStatus::Pending,
        ]);
    }
}
