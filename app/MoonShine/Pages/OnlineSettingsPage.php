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

class OnlineSettingsPage extends Page
{
    public function getTitle(): string
    {
        return 'Онлайн на сайте';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $values = [
            'online_enabled' => (bool) Setting::get('online.enabled', false),
            'online_min' => (int) Setting::get('online.min', 1500),
            'online_max' => (int) Setting::get('online.max', 1600),
            'online_tick_seconds' => (int) Setting::get('online.tick_seconds', 8),
            'online_max_step' => (int) Setting::get('online.max_step', 3),
        ];

        return [
            FormBuilder::make('/admin/online-settings')
                ->fields([
                    Box::make('Накрутка', [
                        Switcher::make('Включить накрутку', 'online_enabled')
                            ->hint('Если выключено, в шапке показывается только реальное число активных пользователей.'),
                    ]),
                    Box::make('Диапазон', [
                        Number::make('Минимум', 'online_min')->required(),
                        Number::make('Максимум', 'online_max')->required(),
                    ]),
                    Box::make('Поведение', [
                        Number::make('Частота обновления (сек)', 'online_tick_seconds')->min(3)->max(60)->required(),
                        Number::make('Максимальный шаг', 'online_max_step')->min(1)->max(10)->required(),
                    ]),
                ])
                ->fill($values)
                ->submit('Сохранить')
                ->async(),
        ];
    }
}
