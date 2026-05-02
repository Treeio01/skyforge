<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User\Pages;

use App\Models\User;
use App\Models\UtmMark;
use App\MoonShine\Resources\User\UserResource;
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
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<UserResource>
 */
class UserIndexPage extends IndexPage
{
    protected bool $isLazy = true;

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
            Number::make('Баланс', 'balance')
                ->modifyRawValue(MoneyFormatter::field()),
            Number::make('Модиф.', 'chance_modifier'),
            Text::make('UTM', formatted: fn ($item) => $item->utmMark?->slug ?? '—'),
            Switcher::make('Бан', 'is_banned'),
            Switcher::make('Админ', 'is_admin'),
            Switcher::make('Бот', 'is_bot'),
            Date::make('Активность', 'last_active_at'),
            Date::make('Регистрация', 'created_at'),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->prepend(
                ActionButton::make('Забанить', fn ($item) => route('moonshine.users.ban', $item))
                    ->canSee(fn ($item) => ! (bool) $item?->is_banned)
                    ->withConfirm(
                        title: 'Забанить пользователя?',
                        content: 'Пользователь не сможет играть, выводить и пополнять. Действие можно отменить.',
                        button: 'Забанить',
                        fields: [
                            Text::make('Причина (опционально)', 'reason'),
                        ],
                    )
                    ->error(),
                ActionButton::make('Разбанить', fn ($item) => route('moonshine.users.unban', $item))
                    ->canSee(fn ($item) => (bool) $item?->is_banned)
                    ->withConfirm(
                        title: 'Разбанить пользователя?',
                        content: 'Доступ будет восстановлен.',
                        button: 'Разбанить',
                    )
                    ->primary(),
            );
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Switcher::make('Бан', 'is_banned'),
            Switcher::make('Админ', 'is_admin'),
            Switcher::make('Бот', 'is_bot'),
            Select::make('UTM-метка', 'utm_mark_id')
                ->options(UtmMark::query()->orderBy('slug')->pluck('slug', 'id')->all())
                ->nullable(),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        // Default tag hides bots so admin metrics/lists reflect real players.
        return [
            QueryTag::make('Все', fn ($q) => $q->where('is_bot', false))->default(),
            QueryTag::make('Активные 7д', fn ($q) => $q->where('is_bot', false)->where('last_active_at', '>=', now()->subWeek())),
            QueryTag::make('Забаненные', fn ($q) => $q->where('is_bot', false)->where('is_banned', true)),
            QueryTag::make('Без trade URL', fn ($q) => $q->where('is_bot', false)->whereNull('trade_url')),
            QueryTag::make('Админы', fn ($q) => $q->where('is_bot', false)->where('is_admin', true)),
            QueryTag::make('Стримеры', fn ($q) => $q->where('is_bot', false)->where('chance_modifier', '>', 1)),
            QueryTag::make('Подозрительные', fn ($q) => $q->where('is_bot', false)->where('chance_modifier', '<', 1)),
            QueryTag::make('Custom edge', fn ($q) => $q->where('is_bot', false)->whereNotNull('house_edge_override')),
            QueryTag::make('Боты', fn ($q) => $q->where('is_bot', true)),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        $base = User::query()->where('is_bot', false);
        $total = (clone $base)->count();
        $active24h = (clone $base)->where('last_active_at', '>=', now()->subDay())->count();
        $active7d = (clone $base)->where('last_active_at', '>=', now()->subWeek())->count();
        $banned = (clone $base)->where('is_banned', true)->count();
        $noTradeUrl = (clone $base)->whereNull('trade_url')->count();

        return [
            ValueMetric::make('Всего')->value($total)->columnSpan(3, 12),
            ValueMetric::make('Активны 24ч')->value($active24h)->columnSpan(3, 12),
            ValueMetric::make('Активны 7д')->value($active7d)->columnSpan(2, 12),
            ValueMetric::make('Забанены')->value($banned)->columnSpan(2, 12),
            ValueMetric::make('Без trade URL')->value($noTradeUrl)->columnSpan(2, 12),
        ];
    }
}
