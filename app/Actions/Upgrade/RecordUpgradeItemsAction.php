<?php

declare(strict_types=1);

namespace App\Actions\Upgrade;

use App\Models\Upgrade;
use App\Models\UpgradeItem;
use App\Models\UserSkin;
use Illuminate\Database\Eloquent\Collection;

class RecordUpgradeItemsAction
{
    /** @param Collection<int, UserSkin> $betSkins */
    public function execute(Upgrade $upgrade, Collection $betSkins): void
    {
        foreach ($betSkins as $betSkin) {
            UpgradeItem::create([
                'upgrade_id' => $upgrade->id,
                'user_skin_id' => $betSkin->id,
                'skin_id' => $betSkin->skin_id,
                'price' => $betSkin->price_at_acquisition,
            ]);
        }
    }
}
