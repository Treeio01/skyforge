<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Withdrawal\Pages;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use App\MoonShine\Pages\Concerns\HasExportButton;
use App\MoonShine\Resources\Withdrawal\WithdrawalResource;
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
 * @extends IndexPage<WithdrawalResource>
 */
class WithdrawalIndexPage extends IndexPage
{
    use HasExportButton;

    protected bool $isLazy = true;

    protected function exportRoute(): string
    {
        return route('moonshine.export.withdrawals');
    }

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Пользователь', formatted: fn ($item) => $item->user?->username ?? 'ID:'.$item->user_id),
            Number::make('Сумма', 'amount')->modifyRawValue(MoneyFormatter::field()),
            Text::make('Статус', formatted: fn ($item) => match ((string) ($item->status?->value ?? $item->status)) {
                'pending' => '⏳ Ожидает',
                'processing' => '🔄 Обработка',
                'completed' => '✅ Завершён',
                'failed' => '❌ Ошибка',
                'cancelled' => '🚫 Отменён',
                default => (string) ($item->status?->value ?? $item->status),
            }),
            Text::make('Trade Offer', 'trade_offer_id'),
            Date::make('Завершён', 'completed_at'),
            Date::make('Создан', 'created_at'),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons()
            ->prepend(
                ActionButton::make('Подтвердить', fn ($item) => route('moonshine.withdrawals.approve', $item))
                    ->method('post')
                    ->canSee(fn ($item) => \in_array(
                        $item?->status?->value,
                        [WithdrawalStatus::Pending->value, WithdrawalStatus::Processing->value],
                        true,
                    ))
                    ->withConfirm(
                        title: 'Подтвердить вывод?',
                        content: 'Статус будет переведён в Completed.',
                        button: 'Подтвердить',
                    )
                    ->primary(),
                ActionButton::make('Отклонить', fn ($item) => route('moonshine.withdrawals.reject', $item))
                    ->method('post')
                    ->canSee(fn ($item) => $item?->status?->value !== WithdrawalStatus::Completed->value)
                    ->withConfirm(
                        title: 'Отклонить вывод?',
                        content: 'Статус будет переведён в Cancelled.',
                        button: 'Отклонить',
                        fields: [
                            Text::make('Причина отказа', 'reason'),
                        ],
                    )
                    ->error(),
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
                    'sent' => 'Отправлен',
                    'completed' => 'Завершён',
                    'failed' => 'Ошибка',
                    'cancelled' => 'Отменён',
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
                'Ожидают',
                fn ($q) => $q->whereIn('status', [WithdrawalStatus::Pending->value, WithdrawalStatus::Processing->value]),
            ),
            QueryTag::make(
                'Завершены за 24ч',
                fn ($q) => $q->where('status', WithdrawalStatus::Completed->value)->where('completed_at', '>=', now()->subDay()),
            ),
            QueryTag::make(
                'Отклонены за 7д',
                fn ($q) => $q->whereIn('status', [WithdrawalStatus::Cancelled->value, WithdrawalStatus::Failed->value])->where('updated_at', '>=', now()->subWeek()),
            ),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        $pendingCount = Withdrawal::whereIn('status', [WithdrawalStatus::Pending->value, WithdrawalStatus::Processing->value])->count();
        $pendingSum = (int) Withdrawal::whereIn('status', [WithdrawalStatus::Pending->value, WithdrawalStatus::Processing->value])->sum('amount');
        $completedTodayCount = Withdrawal::where('status', WithdrawalStatus::Completed->value)
            ->where('completed_at', '>=', now()->startOfDay())
            ->count();

        return [
            ValueMetric::make('Ожидают обработки')->value($pendingCount)->columnSpan(4, 12),
            ValueMetric::make('Сумма ожидающих')->value(MoneyFormatter::format($pendingSum))->columnSpan(4, 12),
            ValueMetric::make('Завершено сегодня')->value($completedTodayCount)->columnSpan(4, 12),
        ];
    }
}
