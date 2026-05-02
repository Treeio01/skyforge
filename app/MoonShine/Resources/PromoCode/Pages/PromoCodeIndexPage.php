<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCode\Pages;

use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<PromoCodeResource>
 */
class PromoCodeIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Код', 'code'),
            Text::make('Тип', formatted: fn ($item) => match ($item->type) {
                'balance' => 'Баланс',
                'deposit_bonus' => 'Бонус депозита',
                default => $item->type,
            }),
            Text::make('Сумма / %', formatted: fn ($item) => $item->type === 'deposit_bonus'
                ? $item->amount.'%'
                : MoneyFormatter::format((int) $item->amount)),
            Number::make('Макс. исп.', 'max_uses'),
            Number::make('Использован', 'times_used'),
            Switcher::make('Активен', 'is_active'),
            Date::make('Истекает', 'expires_at'),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->add(
                ActionButton::make('Активировать', fn () => route('moonshine.promo-codes.bulk-activate'))
                    ->bulk()
                    ->withConfirm(title: 'Активировать выбранные промокоды?', button: 'Активировать')
                    ->primary(),
                ActionButton::make('Деактивировать', fn () => route('moonshine.promo-codes.bulk-deactivate'))
                    ->bulk()
                    ->withConfirm(title: 'Деактивировать выбранные промокоды?', button: 'Деактивировать')
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
        return [
            QueryTag::make('Все', fn ($q) => $q),
            QueryTag::make('Активные', fn ($q) => $q->where('is_active', true)),
            QueryTag::make('Истёкшие', fn ($q) => $q->whereNotNull('expires_at')->where('expires_at', '<', now())),
            QueryTag::make('Исчерпаны', fn ($q) => $q->whereColumn('times_used', '>=', 'max_uses')->whereNotNull('max_uses')),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        $total = PromoCode::query()->count();
        $active = PromoCode::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();
        $totalUses = (int) PromoCodeUsage::query()->count();
        $balanceGiven = (int) PromoCodeUsage::query()
            ->whereHas('promoCode', fn ($q) => $q->where('type', 'balance'))
            ->sum('amount');

        return [
            ValueMetric::make('Всего')->value($total)->columnSpan(3, 12),
            ValueMetric::make('Активных')->value($active)->columnSpan(3, 12),
            ValueMetric::make('Использований')->value($totalUses)->columnSpan(3, 12),
            ValueMetric::make('Бонус выдан')->value(MoneyFormatter::format($balanceGiven))->columnSpan(3, 12),
        ];
    }
}
