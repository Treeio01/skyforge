<?php

declare(strict_types=1);

namespace App\Data\Skin;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class IndexSkinsData extends Data
{
    public function __construct(
        public string|Optional $category,
        public string|Optional $search,
        #[IntegerType, Min(0)]
        public int|Optional $min_price,
        #[IntegerType, Min(0)]
        public int|Optional $max_price,
        #[In(['price', 'name'])]
        public string|Optional $sort,
        #[In(['asc', 'desc'])]
        public string|Optional $direction,
        #[IntegerType, Min(1), Max(200)]
        public int|Optional $per_page,
    ) {}
}
