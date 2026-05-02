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
            // Game
            ['key' => 'house_edge', 'value' => '5.00', 'type' => 'float', 'description' => 'Комиссия казино (%)'],
            ['key' => 'min_bet_amount', 'value' => '100', 'type' => 'integer', 'description' => 'Минимальная ставка (копейки)'],
            ['key' => 'max_bet_amount', 'value' => '100000000', 'type' => 'integer', 'description' => 'Максимальная ставка (копейки)'],
            ['key' => 'min_upgrade_chance', 'value' => '1.00', 'type' => 'float', 'description' => 'Минимальный шанс апгрейда (%)'],
            ['key' => 'max_upgrade_chance', 'value' => '95.00', 'type' => 'float', 'description' => 'Максимальный шанс апгрейда (%)'],
            ['key' => 'upgrade_cooldown', 'value' => '2', 'type' => 'integer', 'description' => 'Кулдаун между апгрейдами (сек)'],

            // Site
            ['key' => 'site_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Сайт включён'],
            ['key' => 'withdrawals_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Выводы включены'],
            ['key' => 'maintenance_message', 'value' => '', 'type' => 'string', 'description' => 'Сообщение техработ'],

            // SEO
            ['key' => 'seo_title', 'value' => 'GROWSKINS — Апгрейд скинов CS2', 'type' => 'string', 'description' => 'SEO заголовок (OG title)'],
            ['key' => 'seo_description', 'value' => 'Улучшайте свои предметы CS2 в пару кликов с системой Provably Fair. Честная игра, моментальные выводы.', 'type' => 'string', 'description' => 'SEO описание (meta description)'],
            ['key' => 'seo_keywords', 'value' => 'CS2 скины, апгрейд скинов, CS2 upgrade, CSGO скины, Provably Fair, рулетка скинов', 'type' => 'string', 'description' => 'SEO ключевые слова'],

            // Socials
            ['key' => 'social_vk', 'value' => 'https://vk.com/skyforge', 'type' => 'string', 'description' => 'Ссылка ВКонтакте'],
            ['key' => 'social_telegram', 'value' => 'https://t.me/skyforge', 'type' => 'string', 'description' => 'Ссылка Telegram'],
            ['key' => 'social_discord', 'value' => 'https://discord.gg/skyforge', 'type' => 'string', 'description' => 'Ссылка Discord'],
            ['key' => 'social_tiktok', 'value' => 'https://tiktok.com/@skyforge', 'type' => 'string', 'description' => 'Ссылка TikTok'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/@skyforge', 'type' => 'string', 'description' => 'Ссылка YouTube'],
            ['key' => 'social_twitch', 'value' => 'https://twitch.tv/skyforge', 'type' => 'string', 'description' => 'Ссылка Twitch'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [...$setting, 'updated_at' => now()],
            );
        }
    }
}
