<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\UserSkinSource;
use App\Enums\UserSkinStatus;
use App\Models\Skin;
use App\Models\User;
use App\Models\UserSkin;

class AddSkinToInventoryAction
{
    public function execute(
        User $user,
        Skin $skin,
        UserSkinSource $source,
        ?int $priceAtAcquisition = null,
        ?int $sourceId = null,
    ): UserSkin {
        return UserSkin::create([
            'user_id' => $user->id,
            'skin_id' => $skin->id,
            'price_at_acquisition' => $priceAtAcquisition ?? (int) $skin->price,
            'source' => $source,
            'source_id' => $sourceId,
            'status' => UserSkinStatus::Available,
        ]);
    }
}
