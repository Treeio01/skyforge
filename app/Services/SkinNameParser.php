<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SkinCategory;
use App\Enums\SkinExterior;

class SkinNameParser
{
    /**
     * Parse market_hash_name into components.
     *
     * Examples:
     *   "AK-47 | Redline (Field-Tested)" → weapon: AK-47, skin: Redline, exterior: FT, category: weapon
     *   "★ Karambit | Doppler (Factory New)" → weapon: Karambit, skin: Doppler, exterior: FN, category: knife
     *   "Sticker | karrigan | Paris 2023" → skin: karrigan | Paris 2023, category: sticker
     *
     * @return array{weapon_type: ?string, skin_name: ?string, exterior: ?string, category: string}
     */
    public static function parse(string $name): array
    {
        $category = self::detectCategory($name);
        $exterior = self::extractExterior($name);

        $cleanName = preg_replace('/\s*\([^)]*\)\s*$/', '', $name);
        $cleanName = ltrim($cleanName, "★ \t\n\r\0\x0B");

        if (in_array($category, [SkinCategory::Sticker->value, SkinCategory::Graffiti->value, SkinCategory::Container->value])) {
            $parts = explode(' | ', $cleanName, 2);

            return [
                'weapon_type' => null,
                'skin_name' => $parts[1] ?? $parts[0],
                'exterior' => $exterior,
                'category' => $category,
            ];
        }

        if (str_contains($cleanName, ' | ')) {
            [$weaponType, $skinName] = explode(' | ', $cleanName, 2);

            return [
                'weapon_type' => $weaponType,
                'skin_name' => $skinName,
                'exterior' => $exterior,
                'category' => $category,
            ];
        }

        return [
            'weapon_type' => null,
            'skin_name' => $cleanName,
            'exterior' => $exterior,
            'category' => $category,
        ];
    }

    private static function detectCategory(string $name): string
    {
        if (str_starts_with($name, 'Sticker |')) {
            return SkinCategory::Sticker->value;
        }

        if (str_starts_with($name, 'Sealed Graffiti |') || str_starts_with($name, 'Graffiti |')) {
            return SkinCategory::Graffiti->value;
        }

        if (str_starts_with($name, '★')) {
            if (str_contains($name, 'Gloves') || str_contains($name, 'Wraps') || str_contains($name, 'Hand Wraps')) {
                return SkinCategory::Gloves->value;
            }

            return SkinCategory::Knife->value;
        }

        if (str_contains($name, 'Charm |')) {
            return SkinCategory::Charm->value;
        }

        if (preg_match('/^(.*Case|.*Capsule|.*Package)$/i', $name)) {
            return SkinCategory::Container->value;
        }

        return SkinCategory::Weapon->value;
    }

    private static function extractExterior(string $name): ?string
    {
        $map = [
            'Factory New' => SkinExterior::FactoryNew->value,
            'Minimal Wear' => SkinExterior::MinimalWear->value,
            'Field-Tested' => SkinExterior::FieldTested->value,
            'Well-Worn' => SkinExterior::WellWorn->value,
            'Battle-Scarred' => SkinExterior::BattleScarred->value,
        ];

        foreach ($map as $label => $value) {
            if (str_contains($name, "({$label})")) {
                return $value;
            }
        }

        return null;
    }
}
