<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCodeUsage\Pages;

use App\MoonShine\Resources\PromoCodeUsage\PromoCodeUsageResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<PromoCodeUsageResource>
 */
class PromoCodeUsageDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Пользователь', formatted: fn ($item) => $item->user?->username ?? 'ID:'.$item->user_id),
            Text::make('Steam ID', formatted: fn ($item) => $item->user?->steam_id ?? '—'),
            Text::make('Промокод', formatted: fn ($item) => $item->promoCode?->code ?? 'ID:'.$item->promo_code_id),
            Number::make('Начислено', 'amount')->modifyRawValue(MoneyFormatter::field()),
            Date::make('Использован', 'created_at'),
        ];
    }
}
