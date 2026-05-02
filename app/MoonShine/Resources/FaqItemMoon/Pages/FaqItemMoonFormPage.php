<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItemMoon\Pages;

use App\Models\FaqCategory;
use App\MoonShine\Resources\FaqItemMoon\FaqItemMoonResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<FaqItemMoonResource>
 */
class FaqItemMoonFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Select::make('Категория', 'faq_category_id')
                    ->options(FaqCategory::pluck('name', 'id')->toArray())
                    ->required(),
                Text::make('Вопрос (RU)', 'question')->required(),
                Textarea::make('Ответ (RU)', 'answer')->required(),
                Text::make('Question (EN)', 'question_en')
                    ->hint('Перевод на английский. Если пусто — для en-локали покажется русская версия.'),
                Textarea::make('Answer (EN)', 'answer_en')
                    ->hint('Перевод на английский. Если пусто — fallback на русскую версию.'),
                Number::make('Порядок', 'sort_order')->hint('Меньше = выше'),
                Switcher::make('Активен', 'is_active'),
            ]),
        ];
    }
}
