<?php

declare(strict_types=1);

namespace App\Enums;

enum SkinRarity: string
{
    case Consumer = 'consumer';
    case Industrial = 'industrial';
    case MilSpec = 'mil_spec';
    case Restricted = 'restricted';
    case Classified = 'classified';
    case Covert = 'covert';
    case Extraordinary = 'extraordinary';
    case Contraband = 'contraband';

    public function color(): string
    {
        return match ($this) {
            self::Consumer => '#b0c3d9',
            self::Industrial => '#5e98d9',
            self::MilSpec => '#4b69ff',
            self::Restricted => '#8847ff',
            self::Classified => '#d32ce6',
            self::Covert => '#eb4b4b',
            self::Extraordinary => '#e4ae39',
            self::Contraband => '#e4ae39',
        };
    }
}
