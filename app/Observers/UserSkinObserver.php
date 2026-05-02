<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\UserSkin;

class UserSkinObserver
{
    public function created(UserSkin $userSkin): void
    {
        $sourceLabel = $userSkin->source?->value ?? 'unknown';
        $skinName = $userSkin->skin?->market_hash_name ?? '#'.$userSkin->skin_id;

        activity('user_skin')
            ->performedOn($userSkin)
            ->withProperties([
                'source' => $sourceLabel,
                'price' => $userSkin->price_at_acquisition,
                'skin_id' => $userSkin->skin_id,
                'user_id' => $userSkin->user_id,
            ])
            ->log("Получен скин «{$skinName}» (источник: {$sourceLabel})");
    }
}
