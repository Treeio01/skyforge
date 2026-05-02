<?php

declare(strict_types=1);

namespace App\Data\ProvablyFair;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class RotateClientSeedData extends Data
{
    public function __construct(
        #[Required, Max(64)]
        public string $client_seed,
    ) {}
}
