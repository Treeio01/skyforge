<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Data\Profile\UpdateTradeUrlData;
use App\Models\User;

class UpdateTradeUrlAction
{
    public function execute(User $user, UpdateTradeUrlData $data): User
    {
        $user->update(['trade_url' => $data->trade_url]);

        return $user;
    }
}
