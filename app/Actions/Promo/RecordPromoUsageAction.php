<?php

declare(strict_types=1);

namespace App\Actions\Promo;

use App\Models\PromoCode;
use App\Models\User;

class RecordPromoUsageAction
{
    public function execute(User $user, PromoCode $promo): void
    {
        $user->promoCodeUsages()->create([
            'promo_code_id' => $promo->id,
            'amount' => $promo->amount,
            'created_at' => now(),
        ]);

        $promo->increment('times_used');
    }
}
