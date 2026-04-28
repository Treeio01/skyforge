<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UtmMark\Pages;

use App\MoonShine\Resources\UtmMark\UtmMarkResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Throwable;

/**
 * @extends DetailPage<UtmMarkResource>
 */
class UtmMarkDetailPage extends DetailPage
{
    public function getTitle(): string
    {
        return 'Просмотр UTM-метки';
    }

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Slug', 'slug'),
            Text::make('Название', 'name'),
            Text::make('UTM Source', 'utm_source'),
            Text::make('UTM Medium', 'utm_medium'),
            Text::make('UTM Campaign', 'utm_campaign'),
            Text::make('UTM Content', 'utm_content'),
            Text::make('UTM Term', 'utm_term'),
            Number::make('Юзеры по этой метке', 'users_count', formatted: fn ($item) => (int) ($item->users_count ?? $item->users()->count())),
            Number::make('Депозиты', 'deposits_count', formatted: fn ($item) => (int) ($item->deposits_count ?? $item->deposits()->count())),
            Number::make('Апгрейды', 'upgrades_count', formatted: fn ($item) => (int) ($item->upgrades_count ?? $item->upgrades()->count())),
            Number::make('Выводы', 'withdrawals_count', formatted: fn ($item) => (int) ($item->withdrawals_count ?? $item->withdrawals()->count())),
            Switcher::make('Активна', 'is_active'),
            Switcher::make('Создана админом', 'is_admin_created'),
            Textarea::make('Заметка', 'notes'),
            Date::make('Создана', 'created_at')->format('d.m.Y H:i'),
            Date::make('Обновлена', 'updated_at')->format('d.m.Y H:i'),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyDetailComponent(ComponentContract $component): ComponentContract
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
