<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Upgrade\Pages;

use App\MoonShine\Resources\Upgrade\UpgradeResource;
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
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends IndexPage<UpgradeResource>
 */
class UpgradeIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Игрок', formatted: fn ($item) => $item->user?->username ?? '—'),
            Number::make('Ставка', 'bet_amount')
                ->modifyRawValue(fn (mixed $value) => number_format(((int) $value) / 100, 2, '.', ' ').' ₽'),
            Text::make('Скин', formatted: fn ($item) => mb_substr($item->targetSkin?->market_hash_name ?? '—', 0, 35)),
            Number::make('Цена цели', 'target_price')
                ->modifyRawValue(fn (mixed $value) => number_format(((int) $value) / 100, 2, '.', ' ').' ₽'),
            Number::make('Шанс', 'chance')
                ->modifyRawValue(fn (mixed $value) => round((float) $value, 2).'%'),
            Text::make('Результат', formatted: fn ($item) => $item->result?->value === 'win' ? '✅ Победа' : '❌ Проигрыш'),
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
        return [
            Select::make('Результат', 'result')
                ->options(['win' => 'Победа', 'lose' => 'Проигрыш'])
                ->nullable(),
        ];
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
