<?php

declare(strict_types=1);

namespace App\Data\Withdrawal;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CreateWithdrawalData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $user_skin_id,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'user_skin_id' => ['required', 'integer', 'exists:user_skins,id'],
        ];
    }
}
