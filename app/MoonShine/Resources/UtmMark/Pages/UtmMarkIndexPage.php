<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UtmMark\Pages;

use App\MoonShine\Resources\UtmMark\UtmMarkResource;
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
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends IndexPage<UtmMarkResource>
 */
class UtmMarkIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Slug', 'slug')->sortable(),
            Text::make('Название', 'name'),
            Text::make('UTM Source', 'utm_source'),
            Text::make('UTM Campaign', 'utm_campaign'),
            Number::make('Юзеры', 'users_count')->sortable(),
            Number::make('Депозиты', 'deposits_count'),
            Number::make('Апгрейды', 'upgrades_count'),
            Number::make('Выводы', 'withdrawals_count'),
            Switcher::make('Активна', 'is_active'),
            Switcher::make('Создана админом', 'is_admin_created'),
            Date::make('Создана', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        if ($component instanceof TableBuilder) {
            $component->items($component->getItems()->loadCount(['users', 'deposits', 'upgrades', 'withdrawals']));
        }

        return $component;
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
