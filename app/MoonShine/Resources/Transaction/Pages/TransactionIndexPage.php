<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction\Pages;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\MoonShine\Resources\Transaction\TransactionResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends IndexPage<TransactionResource>
 */
class TransactionIndexPage extends IndexPage
{
    protected bool $isLazy = true;

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
            Number::make('Сумма', 'amount')
                ->modifyRawValue(fn (mixed $value) => number_format(((int) $value) / 100, 2, '.', ' ').' ₽'),
            Number::make('До', 'balance_before')
                ->modifyRawValue(fn (mixed $value) => number_format(((int) $value) / 100, 2, '.', ' ').' ₽'),
            Number::make('После', 'balance_after')
                ->modifyRawValue(fn (mixed $value) => number_format(((int) $value) / 100, 2, '.', ' ').' ₽'),
            Date::make('Дата', 'created_at'),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function topRightButtons(): ListOf
    {
        return parent::topRightButtons()
            ->prepend(
                ActionButton::make('Экспорт CSV', fn () => route('moonshine.export.transactions'))
                    ->customAttributes(['target' => '_blank'])
                    ->icon('arrow-down-tray'),
            );
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
            ValueMetric::make('Депозиты сегодня')
                ->value(number_format($depositSum / 100, 2, '.', ' ').' ₽')
                ->columnSpan(3, 12),
            ValueMetric::make('Выводы сегодня')
                ->value(number_format(\abs($withdrawalSum) / 100, 2, '.', ' ').' ₽')
                ->columnSpan(3, 12),
            ValueMetric::make('Ставки сегодня')
                ->value(number_format(\abs($betSum) / 100, 2, '.', ' ').' ₽')
                ->columnSpan(3, 12),
        ];
    }

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
