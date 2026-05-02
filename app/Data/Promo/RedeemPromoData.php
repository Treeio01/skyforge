<?php

declare(strict_types=1);

namespace App\Data\Promo;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class RedeemPromoData extends Data
{
    public function __construct(
        #[Required, Max(50)]
        public string $code,
    ) {}
}
