<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCode\Pages;

use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<PromoCodeResource>
 */
class PromoCodeDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Код', 'code'),
            Text::make('Тип', 'type'),
            Number::make('Сумма / Процент', 'amount'),
            Number::make('Мин. депозит', 'min_deposit')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Макс. использований', 'max_uses'),
            Number::make('Использован', 'times_used'),
            Switcher::make('Активен', 'is_active'),
            Date::make('Истекает', 'expires_at'),
            Date::make('Создан', 'created_at'),
            Date::make('Обновлён', 'updated_at'),
        ];
    }
}
