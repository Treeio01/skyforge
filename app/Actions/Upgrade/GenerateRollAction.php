<?php

declare(strict_types=1);

namespace App\Actions\Upgrade;

use App\DTOs\RollResultDTO;

class GenerateRollAction
{
    public function execute(string $serverSeed, string $clientSeed, int $nonce): RollResultDTO
    {
        $hmac = hash_hmac('sha256', "{$clientSeed}-{$nonce}", $serverSeed);
        $hex = substr($hmac, 0, 8);
        $int = hexdec($hex);
        $value = $int / 0xFFFFFFFF;

        return new RollResultDTO(value: $value, hex: $hex);
    }
}
