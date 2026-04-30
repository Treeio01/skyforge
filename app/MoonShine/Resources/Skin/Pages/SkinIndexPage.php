<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Skin\Pages;

use App\Enums\SkinCategory;
use App\MoonShine\Resources\Skin\SkinResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<SkinResource>
 */
class SkinIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Название', 'market_hash_name'),
            Text::make('Оружие', 'weapon_type'),
            Text::make('Категория', formatted: fn ($item) => $item->category?->value ?? ''),
            Number::make('Цена', 'price')
                ->modifyRawValue(MoneyFormatter::field()),
            Switcher::make('Активен', 'is_active'),
            Switcher::make('Для апгрейда', 'is_available_for_upgrade'),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->add(
                ActionButton::make('Активировать', fn () => route('moonshine.skins.bulk-activate'))
                    ->bulk()
                    ->withConfirm(title: 'Активировать выбранные скины?', button: 'Активировать')
                    ->primary(),
                ActionButton::make('Деактивировать', fn () => route('moonshine.skins.bulk-deactivate'))
                    ->bulk()
                    ->withConfirm(title: 'Деактивировать выбранные скины?', button: 'Деактивировать')
                    ->error(),
            );
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('Категория', 'category')
                ->options(array_combine(
                    array_column(SkinCategory::cases(), 'value'),
                    array_column(SkinCategory::cases(), 'value'),
                ))
                ->nullable(),
            Switcher::make('Активен', 'is_active'),
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
}
