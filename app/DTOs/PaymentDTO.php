<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class PaymentDTO
{
    public function __construct(
        public int $amount,
        public string $method,
        public string $providerId,
        public ?string $redirectUrl = null,
    ) {}
}
