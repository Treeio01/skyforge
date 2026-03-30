<?php

declare(strict_types=1);

namespace App\Enums;

enum SkinCategory: string
{
    case Weapon = 'weapon';
    case Knife = 'knife';
    case Gloves = 'gloves';
    case Sticker = 'sticker';
    case Graffiti = 'graffiti';
    case Charm = 'charm';
    case Agent = 'agent';
    case Container = 'container';
    case Other = 'other';
}
