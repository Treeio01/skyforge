<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\UserSkinStatus;
use Illuminate\Database\Eloquent\Collection;

class MarkSkinsBurnedAction
{
    /** @param Collection<int, \App\Models\UserSkin> $skins */
    public function execute(Collection $skins): int
    {
        foreach ($skins as $skin) {
            $skin->update(['status' => UserSkinStatus::Burned]);
        }

        return $skins->count();
    }
}
