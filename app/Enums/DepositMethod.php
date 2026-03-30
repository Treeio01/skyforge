<?php

declare(strict_types=1);

namespace App\Enums;

enum DepositMethod: string
{
    case Skins = 'skins';
    case Sbp = 'sbp';
    case Crypto = 'crypto';
}
