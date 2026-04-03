<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class WebhookDTO
{
    /** @param array<string, mixed> $rawData */
    public function __construct(
        public string $providerId,
        public string $status,
        public int $amount,
        public bool $signatureValid,
        public array $rawData = [],
    ) {}
}
