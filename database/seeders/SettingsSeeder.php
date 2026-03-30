<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'house_edge', 'value' => '5.00', 'type' => 'float', 'description' => 'House edge percentage for upgrade calculations'],
            ['key' => 'min_bet_amount', 'value' => '100', 'type' => 'integer', 'description' => 'Minimum bet amount in kopecks (1 RUB)'],
            ['key' => 'max_bet_amount', 'value' => '5000000', 'type' => 'integer', 'description' => 'Maximum bet amount in kopecks (50K RUB)'],
            ['key' => 'min_upgrade_chance', 'value' => '1.00', 'type' => 'float', 'description' => 'Minimum upgrade chance percentage'],
            ['key' => 'max_upgrade_chance', 'value' => '95.00', 'type' => 'float', 'description' => 'Maximum upgrade chance percentage'],
            ['key' => 'upgrade_cooldown', 'value' => '2', 'type' => 'integer', 'description' => 'Cooldown between upgrades in seconds'],
            ['key' => 'site_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable/disable the site'],
            ['key' => 'withdrawals_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable/disable withdrawals'],
            ['key' => 'maintenance_message', 'value' => '', 'type' => 'string', 'description' => 'Maintenance message shown to users'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [...$setting, 'updated_at' => now()],
            );
        }
    }
}
