<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Skin\Pages;

use App\MoonShine\Resources\Skin\SkinResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<SkinResource>
 */
class SkinFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('market_hash_name')->readonly(),
                Number::make('price'),
                Switcher::make('is_active'),
                Switcher::make('is_available_for_upgrade'),
            ]),
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }
}
