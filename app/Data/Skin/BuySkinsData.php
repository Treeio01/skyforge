<?php

declare(strict_types=1);

namespace App\Data\Skin;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class BuySkinsData extends Data
{
    public function __construct(
        /** @var array<int, int> */
        #[Required, ArrayType, Min(1)]
        public array $skin_ids,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'skin_ids.*' => ['integer', 'exists:skins,id'],
        ];
    }
}
