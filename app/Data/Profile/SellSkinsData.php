<?php

declare(strict_types=1);

namespace App\Data\Profile;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class SellSkinsData extends Data
{
    public function __construct(
        #[Required, In(['all', 'selected'])]
        public string $mode,

        /** @var array<int, int>|null */
        public ?array $ids = null,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'ids.*' => ['integer'],
        ];
    }
}
