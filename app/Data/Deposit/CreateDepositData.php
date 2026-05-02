<?php

declare(strict_types=1);

namespace App\Data\Deposit;

use App\Enums\DepositMethod;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class CreateDepositData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $amount,
        #[Required, WithCast(EnumCast::class)]
        public DepositMethod $method,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'amount' => [
                'required',
                'integer',
                'min:'.config('skyforge.min_bet_amount'),
                'max:'.config('skyforge.max_bet_amount'),
            ],
            'method' => ['required', 'string', 'in:sbp,crypto'],
        ];
    }
}
