<?php

declare(strict_types=1);

namespace App\Enums;

enum UserSkinStatus: string
{
    case Available = 'available';
    case InUpgrade = 'in_upgrade';
    case Withdrawn = 'withdrawn';
    case Burned = 'burned';
}
