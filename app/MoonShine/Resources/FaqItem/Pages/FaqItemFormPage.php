<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItem\Pages;

use App\MoonShine\Resources\FaqItem\FaqItemResource;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<FaqItemResource>
 */
class FaqItemFormPage extends FormPage
{
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Select::make('Категория', 'category')
                    ->options([
                        'provably' => 'Provably Fair',
                        'upgrade' => 'Апгрейд',
                        'deposit' => 'Пополнение',
                        'withdraw' => 'Вывод',
                        'account' => 'Аккаунт',
                        'other' => 'Другое',
                    ])
                    ->required(),
                Text::make('Вопрос', 'question')->required(),
                Textarea::make('Ответ', 'answer')->required(),
                Number::make('Порядок', 'sort_order')->hint('Меньше = выше'),
                Switcher::make('Активен', 'is_active'),
            ]),
        ];
    }

    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
    {
        return $component;
    }
}
