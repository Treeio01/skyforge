<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\SkinPrice;
use Illuminate\Support\Facades\Cache;

class SkinPriceObserver
{
    public function created(SkinPrice $skinPrice): void
    {
        Cache::forget('market.skin.'.$skinPrice->skin_id);
    }
}
