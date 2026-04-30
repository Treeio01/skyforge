<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItemMoon\Pages;

use App\MoonShine\Resources\FaqItemMoon\FaqItemMoonResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<FaqItemMoonResource>
 */
class FaqItemMoonDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Категория', formatted: fn ($item) => $item->faqCategory?->name ?? $item->category ?? '—'),
            Text::make('Вопрос', 'question'),
            Textarea::make('Ответ', 'answer'),
            Number::make('Порядок', 'sort_order'),
            Switcher::make('Активен', 'is_active'),
            Date::make('Создан', 'created_at'),
            Date::make('Обновлён', 'updated_at'),
        ];
    }
}
