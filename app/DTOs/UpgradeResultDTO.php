<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Upgrade;

readonly class UpgradeResultDTO
{
    public function __construct(
        public Upgrade $upgrade,
    ) {}
}
