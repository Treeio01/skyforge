<?php

declare(strict_types=1);

namespace App\Enums;

enum UserSkinSource: string
{
    case Deposit = 'deposit';
    case UpgradeWin = 'upgrade_win';
    case Admin = 'admin';
}
