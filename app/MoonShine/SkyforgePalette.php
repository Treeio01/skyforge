<?php

declare(strict_types=1);

namespace App\MoonShine;

use MoonShine\Contracts\ColorManager\PaletteContract;

/**
 * Dark palette matching SKYFORGE site (#070A10 bg, #4E89FF accent).
 * Colors in OKLCH format.
 */
final class SkyforgePalette implements PaletteContract
{
    public function getDescription(): string
    {
        return 'SKYFORGE dark blue theme';
    }

    public function getColors(): array
    {
        // Light mode — redirect to dark since site is always dark
        return $this->getDarkColors();
    }

    public function getDarkColors(): array
    {
        return [
            'body' => '0.12 0.02 250',
            'primary' => '0.62 0.18 250',
            'primary-text' => '0.98 0.01 250',
            'secondary' => '0.25 0.04 250',
            'secondary-text' => '0.85 0.03 250',
            'base' => [
                'text' => '0.90 0.02 250',
                'stroke' => '0.62 0.18 250 / 20%',
                'default' => '0.15 0.025 250',
                50 => '0.17 0.025 250',
                100 => '0.20 0.03 250',
                200 => '0.24 0.035 250',
                300 => '0.30 0.05 250',
                400 => '0.38 0.07 250',
                500 => '0.48 0.10 250',
                600 => '0.55 0.14 250',
                700 => '0.62 0.18 250',
                800 => '0.72 0.14 250',
                900 => '0.82 0.10 250',
            ],
            'success' => '0.64 0.22 155',
            'success-text' => '0.80 0.15 155',
            'warning' => '0.80 0.18 80',
            'warning-text' => '0.90 0.10 80',
            'error' => '0.55 0.22 25',
            'error-text' => '0.75 0.20 25',
            'info' => '0.62 0.18 250',
            'info-text' => '0.85 0.08 250',
        ];
    }
}
