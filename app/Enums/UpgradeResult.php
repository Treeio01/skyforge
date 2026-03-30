<?php

declare(strict_types=1);

namespace App\Enums;

enum UpgradeResult: string
{
    case Win = 'win';
    case Lose = 'lose';
}
