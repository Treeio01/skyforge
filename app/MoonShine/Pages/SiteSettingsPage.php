<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Setting;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

class SiteSettingsPage extends Page
{
    public function getTitle(): string
    {
        return 'Настройки сайта';
    }

    public function getBreadcrumbs(): array
    {
        return ['#' => 'Настройки сайта'];
    }

    /** @return list<ComponentContract> */
    protected function components(): iterable
    {
        $values = [
            // Состояние
            'site_enabled' => (bool) Setting::get('site_enabled', true),
            'withdrawals_enabled' => (bool) Setting::get('withdrawals_enabled', true),
            'maintenance_message' => (string) Setting::get('maintenance_message', ''),

            // Игровая логика
            'house_edge' => (float) Setting::get('house_edge', 5),
            'min_upgrade_chance' => (float) Setting::get('min_upgrade_chance', 1),
            'max_upgrade_chance' => (float) Setting::get('max_upgrade_chance', 95),
            'min_bet_amount' => (int) Setting::get('min_bet_amount', 100),
            'max_bet_amount' => (int) Setting::get('max_bet_amount', 5000000),
            'upgrade_cooldown' => (int) Setting::get('upgrade_cooldown', 2),

            // SEO
            'seo_title' => (string) Setting::get('seo_title', ''),
            'seo_description' => (string) Setting::get('seo_description', ''),
            'seo_keywords' => (string) Setting::get('seo_keywords', ''),

            // Соц. сети
            'social_vk' => (string) Setting::get('social_vk', ''),
            'social_telegram' => (string) Setting::get('social_telegram', ''),
            'social_discord' => (string) Setting::get('social_discord', ''),
            'social_tiktok' => (string) Setting::get('social_tiktok', ''),
            'social_youtube' => (string) Setting::get('social_youtube', ''),
            'social_twitch' => (string) Setting::get('social_twitch', ''),
        ];

        return [
            FormBuilder::make(route('moonshine.site-settings.save'))
                ->fields([
                    Box::make('Состояние сайта', [
                        Switcher::make('Сайт включён', 'site_enabled')
                            ->hint('При выключении пользователи увидят страницу обслуживания.'),
                        Switcher::make('Выводы включены', 'withdrawals_enabled'),
                        Textarea::make('Сообщение в режиме обслуживания', 'maintenance_message'),
                    ]),

                    Box::make('Игровая логика', [
                        Number::make('Комиссия дома (%)', 'house_edge')->step(0.1)->required(),
                        Number::make('Минимальный шанс апгрейда (%)', 'min_upgrade_chance')->step(0.1)->required(),
                        Number::make('Максимальный шанс апгрейда (%)', 'max_upgrade_chance')->step(0.1)->required(),
                        Number::make('Минимальная ставка (копейки)', 'min_bet_amount')->required()
                            ->hint('1 ₽ = 100 копеек.'),
                        Number::make('Максимальная ставка (копейки)', 'max_bet_amount')->required(),
                        Number::make('Кулдаун между апгрейдами (сек)', 'upgrade_cooldown')->min(0)->required(),
                    ]),

                    Box::make('SEO', [
                        Text::make('Title', 'seo_title')
                            ->hint('Заголовок страницы и og:title.'),
                        Textarea::make('Description', 'seo_description'),
                        Textarea::make('Keywords', 'seo_keywords'),
                    ]),

                    Box::make('Социальные сети', [
                        Text::make('VK', 'social_vk'),
                        Text::make('Telegram', 'social_telegram'),
                        Text::make('Discord', 'social_discord'),
                        Text::make('TikTok', 'social_tiktok'),
                        Text::make('YouTube', 'social_youtube'),
                        Text::make('Twitch', 'social_twitch'),
                    ]),
                ])
                ->fill($values)
                ->submit('Сохранить')
                ->async(),
        ];
    }
}
