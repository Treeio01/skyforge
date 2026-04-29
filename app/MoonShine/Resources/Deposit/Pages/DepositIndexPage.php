<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Deposit\Pages;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\MoonShine\Pages\Concerns\HasExportButton;
use App\MoonShine\Resources\Deposit\DepositResource;
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
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<DepositResource>
 */
class DepositIndexPage extends IndexPage
{
    use HasExportButton;

    protected bool $isLazy = true;

    protected function exportRoute(): string
    {
        return route('moonshine.export.deposits');
    }

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Пользователь', formatted: fn ($item) => $item->user?->username ?? 'ID:'.$item->user_id),
            Text::make('Метод', formatted: fn ($item) => $item->method?->value ?? (string) $item->method),
            Number::make('Сумма', 'amount')->modifyRawValue(MoneyFormatter::field()),
            Text::make('Статус', formatted: fn ($item) => match ((string) ($item->status?->value ?? $item->status)) {
                'pending' => '⏳ Ожидает',
                'completed' => '✅ Завершён',
                'failed' => '❌ Ошибка',
                'cancelled' => '🚫 Отменён',
                default => (string) ($item->status?->value ?? $item->status),
            }),
            Date::make('Завершён', 'completed_at'),
            Date::make('Создан', 'created_at'),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->prepend(
                ActionButton::make('Завершить', fn ($item) => route('moonshine.deposits.complete', $item))
                    ->method('post')
                    ->canSee(fn ($item) => $item?->status?->value !== DepositStatus::Completed->value)
                    ->withConfirm(
                        title: 'Пометить депозит как завершённый?',
                        content: 'Статус будет переведён в Completed (баланс пользователя нужно проверить отдельно).',
                        button: 'Завершить',
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
            Select::make('Статус', 'status')
                ->options([
                    'pending' => 'Ожидает',
                    'processing' => 'Обработка',
                    'completed' => 'Завершён',
                    'failed' => 'Ошибка',
                    'cancelled' => 'Отменён',
                ])
                ->nullable(),
            Select::make('Метод', 'method')
                ->options([
                    'skins' => 'Skins',
                    'sbp' => 'СБП',
                    'crypto' => 'Crypto',
                ])
                ->nullable(),
            Number::make('Сумма от (₽)', 'amount_from'),
            Number::make('Сумма до (₽)', 'amount_to'),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [
            QueryTag::make('Все', fn ($q) => $q),
            QueryTag::make(
                'Pending',
                fn ($q) => $q->whereIn('status', [DepositStatus::Pending->value, DepositStatus::Processing->value]),
            ),
            QueryTag::make(
                'Completed сегодня',
                fn ($q) => $q->where('status', DepositStatus::Completed->value)->where('completed_at', '>=', now()->startOfDay()),
            ),
            QueryTag::make(
                'Failed за 7д',
                fn ($q) => $q->whereIn('status', [DepositStatus::Failed->value, DepositStatus::Cancelled->value])->where('updated_at', '>=', now()->subWeek()),
            ),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        $todayCount = Deposit::where('status', DepositStatus::Completed->value)
            ->where('completed_at', '>=', now()->startOfDay())
            ->count();
        $todaySum = (int) Deposit::where('status', DepositStatus::Completed->value)
            ->where('completed_at', '>=', now()->startOfDay())
            ->sum('amount');
        $monthSum = (int) Deposit::where('status', DepositStatus::Completed->value)
            ->where('completed_at', '>=', now()->startOfMonth())
            ->sum('amount');
        $pending = Deposit::whereIn('status', [DepositStatus::Pending->value, DepositStatus::Processing->value])->count();

        return [
            ValueMetric::make('Завершено сегодня')->value($todayCount)->columnSpan(3, 12),
            ValueMetric::make('Сумма за сегодня')->value(MoneyFormatter::format($todaySum))->columnSpan(3, 12),
            ValueMetric::make('Сумма за месяц')->value(MoneyFormatter::format($monthSum))->columnSpan(3, 12),
            ValueMetric::make('Pending')->value($pending)->columnSpan(3, 12),
        ];
    }
}
