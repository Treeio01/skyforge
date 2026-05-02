<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UtmMark\Pages;

use App\Models\Deposit;
use App\Models\User;
use App\Models\UtmMark;
use App\MoonShine\Resources\UtmMark\UtmMarkResource;
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
 * @extends IndexPage<UtmMarkResource>
 */
class UtmMarkIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    public function getTitle(): string
    {
        return 'UTM-метки';
    }

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Slug', 'slug')->sortable(),
            Text::make('Название', 'name'),
            Text::make('UTM Source', 'utm_source'),
            Text::make('UTM Campaign', 'utm_campaign'),
            Number::make('Юзеры', 'users_count', formatted: fn ($item) => (int) ($item->users_count ?? 0))->sortable(),
            Number::make('Депозиты', 'deposits_count', formatted: fn ($item) => (int) ($item->deposits_count ?? 0)),
            Number::make('Апгрейды', 'upgrades_count', formatted: fn ($item) => (int) ($item->upgrades_count ?? 0)),
            Number::make('Выводы', 'withdrawals_count', formatted: fn ($item) => (int) ($item->withdrawals_count ?? 0)),
            Text::make('Конверсия', formatted: function ($item) {
                $users = (int) ($item->users_count ?? 0);
                $deposits = (int) ($item->deposits_count ?? 0);

                return $users > 0 ? round($deposits / $users * 100, 1).'%' : '—';
            }),
            Switcher::make('Активна', 'is_active'),
            Switcher::make('Ручная', 'is_admin_created'),
            Date::make('Создана', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->prepend(
                ActionButton::make('🔗 Ссылка', fn ($item) => \rtrim(\config('app.url'), '/').'/?ref='.$item->slug)
                    ->customAttributes(['target' => '_blank', 'title' => 'Открыть реферальную ссылку'])
                    ->icon('link'),
            );
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Switcher::make('Активна', 'is_active'),
            Switcher::make('Ручная', 'is_admin_created'),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [
            QueryTag::make('Все', fn ($q) => $q),
            QueryTag::make('Активные', fn ($q) => $q->where('is_active', true)),
            QueryTag::make('Ручные', fn ($q) => $q->where('is_admin_created', true)),
            QueryTag::make('С конверсией', fn ($q) => $q->has('deposits')),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        $total = UtmMark::query()->count();
        $active = UtmMark::query()->where('is_active', true)->count();
        $usersFromUtm = User::query()->whereNotNull('utm_mark_id')->count();
        $depositingUsers = Deposit::query()
            ->whereHas('user', fn ($q) => $q->whereNotNull('utm_mark_id'))
            ->where('status', 'completed')
            ->distinct('user_id')
            ->count('user_id');
        $conversion = $usersFromUtm > 0
            ? round($depositingUsers / $usersFromUtm * 100, 1).'%'
            : '—';

        return [
            ValueMetric::make('Всего меток')->value($total)->columnSpan(3, 12),
            ValueMetric::make('Активных')->value($active)->columnSpan(3, 12),
            ValueMetric::make('Юзеров с UTM')->value($usersFromUtm)->columnSpan(3, 12),
            ValueMetric::make('Конверсия в депозит')->value($conversion)->columnSpan(3, 12),
        ];
    }
}
