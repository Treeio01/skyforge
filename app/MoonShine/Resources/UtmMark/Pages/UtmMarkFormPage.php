<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UtmMark\Pages;

use App\MoonShine\Resources\UtmMark\UtmMarkResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Throwable;

/**
 * @extends FormPage<UtmMarkResource>
 */
class UtmMarkFormPage extends FormPage
{
    public function getTitle(): string
    {
        $resource = $this->getResource();
        $key = $resource?->getCastedData()?->getKey();

        return $key ? 'Редактирование UTM-метки' : 'Новая UTM-метка';
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make('Идентификация', [
                ID::make(),
                Text::make('Slug', 'slug')
                    ->required()
                    ->hint('Короткий код для ?ref=… ссылок. Уникальный.'),
                Text::make('Название', 'name')
                    ->hint('Внутреннее название для админа.'),
            ]),
            Box::make('UTM-параметры (опционально)', [
                Text::make('UTM Source', 'utm_source'),
                Text::make('UTM Medium', 'utm_medium'),
                Text::make('UTM Campaign', 'utm_campaign'),
                Text::make('UTM Content', 'utm_content'),
                Text::make('UTM Term', 'utm_term'),
            ]),
            Box::make('Состояние', [
                Switcher::make('Активна', 'is_active')
                    ->hint('Если выключено, метка не будет применяться к новым юзерам.'),
                Textarea::make('Заметка', 'notes'),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        $id = $item->getKey();

        return [
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', 'unique:utm_marks,slug'.($id ? ",{$id}" : '')],
            'name' => ['nullable', 'string', 'max:128'],
            'utm_source' => ['nullable', 'string', 'max:128'],
            'utm_medium' => ['nullable', 'string', 'max:128'],
            'utm_campaign' => ['nullable', 'string', 'max:128'],
            'utm_content' => ['nullable', 'string', 'max:128'],
            'utm_term' => ['nullable', 'string', 'max:128'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @param  FormBuilder  $component
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
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
