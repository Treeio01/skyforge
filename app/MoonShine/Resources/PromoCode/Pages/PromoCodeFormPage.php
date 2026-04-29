<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCode\Pages;

use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<PromoCodeResource>
 */
class PromoCodeFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Код', 'code'),
                Select::make('Тип', 'type')
                    ->options([
                        'balance' => 'Баланс (копейки)',
                        'deposit_bonus' => 'Бонус депозита (%)',
                    ]),
                Number::make('Сумма / Процент', 'amount')
                    ->hint('Для баланса — копейки (100 = 1₽). Для бонуса депозита — процент (20 = +20%)'),
                Number::make('Макс. использований', 'max_uses')->hint('Пусто = безлимит'),
                Number::make('Мин. депозит', 'min_deposit')->hint('В копейках'),
                Switcher::make('Активен', 'is_active'),
                Date::make('Истекает', 'expires_at'),
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
