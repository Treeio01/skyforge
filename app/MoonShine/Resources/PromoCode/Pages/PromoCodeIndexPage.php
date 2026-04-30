<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCode\Pages;

use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<PromoCodeResource>
 */
class PromoCodeIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Код', 'code'),
            Text::make('Тип', formatted: fn ($item) => match ($item->type) {
                'balance' => 'Баланс',
                'deposit_bonus' => 'Бонус депозита',
                default => $item->type,
            }),
            Text::make('Сумма / %', formatted: fn ($item) => $item->type === 'deposit_bonus'
                ? $item->amount.'%'
                : MoneyFormatter::format((int) $item->amount)),
            Number::make('Макс. исп.', 'max_uses'),
            Number::make('Использован', 'times_used'),
            Switcher::make('Активен', 'is_active'),
            Date::make('Истекает', 'expires_at'),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->add(
                ActionButton::make('Активировать', fn () => route('moonshine.promo-codes.bulk-activate'))
                    ->bulk()
                    ->withConfirm(title: 'Активировать выбранные промокоды?', button: 'Активировать')
                    ->primary(),
                ActionButton::make('Деактивировать', fn () => route('moonshine.promo-codes.bulk-deactivate'))
                    ->bulk()
                    ->withConfirm(title: 'Деактивировать выбранные промокоды?', button: 'Деактивировать')
                    ->error(),
            );
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }
}
