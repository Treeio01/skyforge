<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction\Pages;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\MoonShine\Pages\Concerns\HasExportButton;
use App\MoonShine\Resources\Transaction\TransactionResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<TransactionResource>
 */
class TransactionIndexPage extends IndexPage
{
    use HasExportButton;

    protected bool $isLazy = true;

    protected function exportRoute(): string
    {
        return route('moonshine.export.transactions');
    }

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Пользователь', formatted: fn ($item) => $item->user?->username ?? 'ID:'.$item->user_id),
            Text::make('Тип', formatted: fn ($item) => match ($item->type?->value ?? $item->type) {
                'deposit' => 'Депозит',
                'withdrawal' => 'Вывод',
                'upgrade_bet' => 'Ставка апгрейда',
                'upgrade_win' => 'Выигрыш апгрейда',
                'refund' => 'Возврат',
                'bonus' => 'Бонус',
                'admin_adjustment' => 'Корректировка',
                default => (string) ($item->type?->value ?? $item->type),
            }),
            Number::make('Сумма', 'amount')->modifyRawValue(MoneyFormatter::field()),
            Number::make('До', 'balance_before')->modifyRawValue(MoneyFormatter::field()),
            Number::make('После', 'balance_after')->modifyRawValue(MoneyFormatter::field()),
            Date::make('Дата', 'created_at'),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('Тип', 'type')
                ->options([
                    'deposit' => 'Депозит',
                    'withdrawal' => 'Вывод',
                    'upgrade_bet' => 'Ставка апгрейда',
                    'upgrade_win' => 'Выигрыш апгрейда',
                    'refund' => 'Возврат',
                    'bonus' => 'Бонус',
                    'admin_adjustment' => 'Корректировка',
                ])
                ->nullable(),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [
            QueryTag::make('Все', fn ($q) => $q),
            QueryTag::make('Депозиты', fn ($q) => $q->where('type', TransactionType::Deposit->value)),
            QueryTag::make('Выводы', fn ($q) => $q->where('type', TransactionType::Withdrawal->value)),
            QueryTag::make('Апгрейды', fn ($q) => $q->whereIn('type', [TransactionType::UpgradeBet->value, TransactionType::UpgradeWin->value])),
            QueryTag::make('Корректировки', fn ($q) => $q->where('type', TransactionType::AdminAdjustment->value)),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        $today = Transaction::query()->where('created_at', '>=', now()->startOfDay());
        $todayCount = (clone $today)->count();
        $depositSum = (int) (clone $today)->where('type', TransactionType::Deposit->value)->sum('amount');
        $withdrawalSum = (int) (clone $today)->where('type', TransactionType::Withdrawal->value)->sum('amount');
        $betSum = (int) (clone $today)->where('type', TransactionType::UpgradeBet->value)->sum('amount');

        return [
            ValueMetric::make('Транзакций сегодня')->value($todayCount)->columnSpan(3, 12),
            ValueMetric::make('Депозиты сегодня')->value(MoneyFormatter::format($depositSum))->columnSpan(3, 12),
            ValueMetric::make('Выводы сегодня')->value(MoneyFormatter::format(abs($withdrawalSum)))->columnSpan(3, 12),
            ValueMetric::make('Ставки сегодня')->value(MoneyFormatter::format(abs($betSum)))->columnSpan(3, 12),
        ];
    }
}
