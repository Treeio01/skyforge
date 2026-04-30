<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User\Pages;

use App\MoonShine\Resources\User\UserResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<UserResource>
 */
class UserDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Preview::make('Аватар', 'avatar_url')->image(),
            Text::make('Никнейм', 'username'),
            Text::make('Steam ID', 'steam_id'),
            Text::make('Trade URL', 'trade_url'),
            Number::make('Баланс', 'balance')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Пополнено', 'total_deposited')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Выведено', 'total_withdrawn')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Апгрейдов на сумму', 'total_upgraded')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Выиграно', 'total_won')->modifyRawValue(MoneyFormatter::field()),
            Text::make('UTM', formatted: fn ($item) => $item->utmMark?->slug ?? '—'),
            Switcher::make('Забанен', 'is_banned'),
            Textarea::make('Причина бана', 'ban_reason'),
            Switcher::make('Админ', 'is_admin'),
            Number::make('Край казино (%)', 'house_edge_override'),
            Number::make('Модификатор шанса', 'chance_modifier'),
            Date::make('Активность', 'last_active_at'),
            Date::make('Регистрация', 'created_at'),
        ];
    }
}
