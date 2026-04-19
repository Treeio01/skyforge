<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItem\Pages;

use App\MoonShine\Resources\FaqItem\FaqItemResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<FaqItemResource>
 */
class FaqItemIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    protected function fields(): iterable
    {
        return [
            ID::make(),
            Select::make('Категория', 'category')
                ->options([
                    'provably' => 'Provably Fair',
                    'upgrade' => 'Апгрейд',
                    'deposit' => 'Пополнение',
                    'withdraw' => 'Вывод',
                    'account' => 'Аккаунт',
                    'other' => 'Другое',
                ]),
            Text::make('Вопрос', 'question'),
            Number::make('Порядок', 'sort_order'),
            Switcher::make('Активен', 'is_active'),
        ];
    }

    protected function filters(): iterable
    {
        return [
            Select::make('Категория', 'category')
                ->options([
                    'provably' => 'Provably Fair',
                    'upgrade' => 'Апгрейд',
                    'deposit' => 'Пополнение',
                    'withdraw' => 'Вывод',
                    'account' => 'Аккаунт',
                    'other' => 'Другое',
                ])
                ->nullable(),
            Switcher::make('Активен', 'is_active'),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }
}
