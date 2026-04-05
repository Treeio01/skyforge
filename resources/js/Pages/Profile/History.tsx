import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { type PageProps } from '@/types';

interface Transaction {
    id: number;
    type: string;
    amount: number;
    balance_after: number;
    description: string | null;
    created_at: string;
}

interface PaginatedTransactions {
    data: Transaction[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface HistoryPageProps extends Record<string, unknown> {
    transactions: PaginatedTransactions;
    filter: string;
}

const TYPE_LABELS: Record<string, string> = {
    all: 'Все',
    deposit: 'Пополнение',
    withdrawal: 'Вывод',
    upgrade_bet: 'Ставка',
    upgrade_win: 'Выигрыш',
};

function formatPrice(kopecks: number): string {
    return (kopecks / 100).toLocaleString('ru-RU', { minimumFractionDigits: 2 }) + ' \u20BD';
}

function typeColor(type: string): string {
    switch (type) {
        case 'deposit':
        case 'upgrade_win':
            return 'text-accent';
        case 'withdrawal':
        case 'upgrade_bet':
            return 'text-[#ef4444]';
        default:
            return 'text-[#f5f5f5]';
    }
}

export default function History() {
    const { transactions, filter } = usePage<PageProps<HistoryPageProps>>().props;

    return (
        <AppLayout>
            <Head title="История" />

            <div className="max-w-3xl mx-auto px-4 py-10 space-y-6">
                <h1 className="text-2xl font-bold">История транзакций</h1>

                {/* Filter Tabs */}
                <div className="flex gap-2 flex-wrap">
                    {Object.entries(TYPE_LABELS).map(([key, label]) => (
                        <Link
                            key={key}
                            href={route('profile.history', key === 'all' ? {} : { type: key })}
                            className={`rounded-lg px-4 py-2 text-sm font-medium transition-colors ${
                                filter === key || (key === 'all' && !filter)
                                    ? 'bg-accent text-[#0a0a0a]'
                                    : 'bg-card border border-border text-[#888888] hover:text-[#f5f5f5] hover:border-border-hover'
                            }`}
                        >
                            {label}
                        </Link>
                    ))}
                </div>

                {/* Transaction List */}
                <div className="space-y-2">
                    {transactions.data.length === 0 ? (
                        <div className="rounded-xl border border-border bg-card p-8 text-center text-[#888888]">
                            Транзакций не найдено
                        </div>
                    ) : (
                        transactions.data.map((tx) => (
                            <div
                                key={tx.id}
                                className="rounded-xl border border-border bg-card px-5 py-4 flex items-center justify-between"
                            >
                                <div>
                                    <span className="text-sm font-medium text-[#888888]">
                                        {TYPE_LABELS[tx.type] ?? tx.type}
                                    </span>
                                    {tx.description && (
                                        <p className="text-xs text-[#888888]/60 mt-0.5">{tx.description}</p>
                                    )}
                                </div>
                                <div className="text-right">
                                    <p className={`text-sm font-bold ${typeColor(tx.type)}`}>
                                        {tx.amount > 0 ? '+' : ''}
                                        {formatPrice(tx.amount)}
                                    </p>
                                    <p className="text-xs text-[#888888]">
                                        {new Date(tx.created_at).toLocaleString('ru-RU')}
                                    </p>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {/* Pagination */}
                {transactions.last_page > 1 && (
                    <div className="flex justify-center gap-3 pt-4">
                        {transactions.prev_page_url && (
                            <Link
                                href={transactions.prev_page_url}
                                className="rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium text-[#888888] hover:text-[#f5f5f5] hover:border-border-hover transition-colors"
                            >
                                Назад
                            </Link>
                        )}
                        <span className="flex items-center text-sm text-[#888888]">
                            {transactions.current_page} / {transactions.last_page}
                        </span>
                        {transactions.next_page_url && (
                            <Link
                                href={transactions.next_page_url}
                                className="rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium text-[#888888] hover:text-[#f5f5f5] hover:border-border-hover transition-colors"
                            >
                                Далее
                            </Link>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
