<?php

declare(strict_types=1);

namespace App\Data\Profile;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UpdateTradeUrlData extends Data
{
    public function __construct(
        #[Required, Max(512), Regex('/^https:\/\/steamcommunity\.com\/tradeoffer\/new\/\?partner=\d+&token=[a-zA-Z0-9_-]+$/')]
        public string $trade_url,
    ) {}

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'trade_url.required' => 'Введите Trade URL.',
            'trade_url.regex' => 'Невалидная Steam Trade URL.',
        ];
    }
}
