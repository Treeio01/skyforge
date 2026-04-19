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
            self::Consumer => '#878F9D',
            self::Industrial => '#356C9D',
            self::MilSpec => '#2D4FFA',
            self::Restricted => '#50318D',
            self::Classified => '#A64BB5',
            self::Covert => '#EA2F2F',
            self::Extraordinary => '#D4AF37',
            self::Contraband => '#D4AF37',
        };
    }
}
