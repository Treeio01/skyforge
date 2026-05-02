<?php

declare(strict_types=1);

namespace App\Actions\Promo;

use App\Models\PromoCode;
use App\Models\User;

class ValidatePromoCodeAction
{
    /** @return array{0: ?PromoCode, 1: ?string} pair of [promo, error_message] */
    public function execute(User $user, string $code): array
    {
        $code = strtoupper(trim($code));

        $promo = PromoCode::query()->where('code', $code)->active()->notExpired()->first();
        if (! $promo) {
            return [null, 'Промокод не найден или истёк'];
        }

        if ($promo->max_uses && $promo->times_used >= $promo->max_uses) {
            return [null, 'Промокод исчерпан'];
        }

        $alreadyUsed = $user->promoCodeUsages()->where('promo_code_id', $promo->id)->exists();
        if ($alreadyUsed) {
            return [null, 'Вы уже использовали этот промокод'];
        }

        return [$promo, null];
    }
}
