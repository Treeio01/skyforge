<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction\Pages;

use App\MoonShine\Resources\Transaction\TransactionResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends IndexPage<TransactionResource>
 */
class TransactionIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Пользователь', formatted: fn ($item) => $item->user?->username ?? 'ID:'.$item->user_id),
            Text::make('Тип', formatted: fn ($item) => match ($item->type?->value ?? $item->type) {
                'deposit' => 'Депозит',
                'withdrawal' => 'Вывод',
                'upgrade_bet' => 'Ставка апгрейда',
                'upgrade_win' => 'Выигрыш апгрейда',
                'refund' => 'Возврат',
                'bonus' => 'Бонус',
                'admin_adjustment' => 'Корректировка',
                default => (string) ($item->type?->value ?? $item->type),
            }),
            Number::make('Сумма', 'amount')
                ->modifyRawValue(fn (mixed $value) => number_format(((int) $value) / 100, 2, '.', ' ').' ₽'),
            Number::make('До', 'balance_before')
                ->modifyRawValue(fn (mixed $value) => number_format(((int) $value) / 100, 2, '.', ' ').' ₽'),
            Number::make('После', 'balance_after')
                ->modifyRawValue(fn (mixed $value) => number_format(((int) $value) / 100, 2, '.', ' ').' ₽'),
            Date::make('Дата', 'created_at'),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return parent::buttons();
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

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
