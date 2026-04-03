<?php

declare(strict_types=1);

namespace App\Actions\Upgrade;

use App\DTOs\ChanceResultDTO;

class CalculateChanceAction
{
    public function execute(
        int $betAmount,
        int $targetPrice,
        float $houseEdge,
        float $chanceModifier,
        float $minChance = 1.00,
        float $maxChance = 95.00,
    ): ChanceResultDTO {
        $baseChance = ($betAmount / $targetPrice) * (1 - $houseEdge / 100) * 100;
        $finalChance = $baseChance * $chanceModifier;
        $clampedChance = max($minChance, min($maxChance, $finalChance));
        $multiplier = $targetPrice / $betAmount;

        return new ChanceResultDTO(
            chance: round($clampedChance, 5),
            multiplier: round($multiplier, 2),
            houseEdge: $houseEdge,
        );
    }
}
