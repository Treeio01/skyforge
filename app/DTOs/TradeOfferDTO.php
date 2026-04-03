<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class TradeOfferDTO
{
    public function __construct(
        public string $tradeOfferId,
        public string $status,
    ) {}
}
