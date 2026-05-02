<?php

declare(strict_types=1);

namespace App\Data\Upgrade;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CreateUpgradeData extends Data
{
    public function __construct(
        /** @var array<int, int> */
        #[ArrayType]
        public array $user_skin_ids,
        #[Required, IntegerType, Min(0)]
        public int $balance_amount,
        #[Required, IntegerType]
        public int $target_skin_id,
    ) {
        $this->user_skin_ids ??= [];
    }

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'user_skin_ids' => ['sometimes', 'array'],
            'user_skin_ids.*' => ['integer', 'exists:user_skins,id'],
            'target_skin_id' => ['required', 'integer', 'exists:skins,id'],
        ];
    }
}
