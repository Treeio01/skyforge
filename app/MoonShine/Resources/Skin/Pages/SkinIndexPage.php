<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Skin\Pages;

use App\Enums\SkinCategory;
use App\Models\Skin;
use App\MoonShine\Resources\Skin\SkinResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
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
        return [
            QueryTag::make('Все', fn ($q) => $q),
            QueryTag::make('Активные', fn ($q) => $q->where('is_active', true)),
            QueryTag::make('Без image', fn ($q) => $q->whereNull('image_path')),
            QueryTag::make('Без цены', fn ($q) => $q->where(fn ($w) => $w->whereNull('price')->orWhere('price', 0))),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        $total = Skin::query()->count();
        $active = Skin::query()->where('is_active', true)->count();
        $forUpgrade = Skin::query()
            ->where('is_active', true)
            ->where('is_available_for_upgrade', true)
            ->count();
        $noImage = Skin::query()->whereNull('image_path')->count();
        $avgPrice = (int) Skin::query()->where('is_active', true)->avg('price');

        return [
            ValueMetric::make('Всего')->value($total)->columnSpan(3, 12),
            ValueMetric::make('Активных')->value($active)->columnSpan(3, 12),
            ValueMetric::make('В апгрейде')->value($forUpgrade)->columnSpan(2, 12),
            ValueMetric::make('Средняя цена')->value(MoneyFormatter::format($avgPrice))->columnSpan(2, 12),
            ValueMetric::make('Без image')->value($noImage)->columnSpan(2, 12),
        ];
    }
}
