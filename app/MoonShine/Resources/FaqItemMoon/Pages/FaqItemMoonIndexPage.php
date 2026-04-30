<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItemMoon\Pages;

use App\MoonShine\Resources\FaqItemMoon\FaqItemMoonResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<FaqItemMoonResource>
 */
class FaqItemMoonIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Категория', formatted: fn ($item) => $item->faqCategory?->name ?? $item->category ?? '—'),
            Text::make('Вопрос', 'question'),
            Number::make('Порядок', 'sort_order'),
            Switcher::make('Активен', 'is_active'),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->add(
                ActionButton::make('Активировать', fn () => route('moonshine.faq.bulk-activate'))
                    ->bulk()
                    ->withConfirm(title: 'Активировать выбранные вопросы?', button: 'Активировать')
                    ->primary(),
                ActionButton::make('Деактивировать', fn () => route('moonshine.faq.bulk-deactivate'))
                    ->bulk()
                    ->withConfirm(title: 'Деактивировать выбранные вопросы?', button: 'Деактивировать')
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

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component instanceof TableBuilder
            ? $component->reorderable(route('moonshine.faq.sort'), 'id')
            : $component;
    }
}
