<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ChanceResultDTO
{
    public function __construct(
        public float $chance,
        public float $multiplier,
        public float $houseEdge,
    ) {}
}
