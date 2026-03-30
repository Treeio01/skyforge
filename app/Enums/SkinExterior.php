<?php

declare(strict_types=1);

namespace App\Enums;

enum SkinExterior: string
{
    case FactoryNew = 'FN';
    case MinimalWear = 'MW';
    case FieldTested = 'FT';
    case WellWorn = 'WW';
    case BattleScarred = 'BS';

    public function label(): string
    {
        return match ($this) {
            self::FactoryNew => 'Factory New',
            self::MinimalWear => 'Minimal Wear',
            self::FieldTested => 'Field-Tested',
            self::WellWorn => 'Well-Worn',
            self::BattleScarred => 'Battle-Scarred',
        };
    }
}
