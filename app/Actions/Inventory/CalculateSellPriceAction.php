<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use Illuminate\Database\Eloquent\Collection;

class CalculateSellPriceAction
{
    /** @param Collection<int, \App\Models\UserSkin> $skins */
    public function execute(Collection $skins): int
    {
        return (int) $skins->sum(fn ($us) => (int) $us->skin->price);
    }
}
