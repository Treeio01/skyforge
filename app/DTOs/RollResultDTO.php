<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class RollResultDTO
{
    public function __construct(
        public float $value,
        public string $hex,
    ) {}
}
