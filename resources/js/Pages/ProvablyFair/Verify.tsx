import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { type PageProps, type Upgrade } from '@/types';

interface VerifyPageProps extends Record<string, unknown> {
    upgrade: Upgrade & {
        client_seed: string;
        server_seed: string;
        server_seed_hash: string;
        nonce: number;
    };
}

export default function Verify() {
    const { upgrade } = usePage<PageProps<VerifyPageProps>>().props;

    function formatPrice(kopecks: number): string {
        return (kopecks / 100).toLocaleString('ru-RU', { minimumFractionDigits: 2 }) + ' \u20BD';
    }

    return (
        <AppLayout>
            <Head title="Проверка апгрейда" />

            <div className="max-w-2xl mx-auto px-4 py-10 space-y-8">
                <h1 className="text-2xl font-bold">Проверка апгрейда #{upgrade.id}</h1>

                {/* Result */}
                <div
                    className={`rounded-xl border p-6 text-center ${
                        upgrade.result === 'win'
                            ? 'border-accent/30 bg-accent-dim'
                            : 'border-[#ef4444]/30 bg-[#ef4444]/10'
                    }`}
                >
                    <p className="text-sm font-medium text-[#888888] mb-1">Результат</p>
                    <p
                        className={`text-3xl font-extrabold ${
                            upgrade.result === 'win' ? 'text-accent' : 'text-[#ef4444]'
                        }`}
                    >
                        {upgrade.result === 'win' ? 'Победа' : 'Проигрыш'}
                    </p>
                </div>

                {/* Upgrade Details */}
                <div className="grid grid-cols-2 gap-4">
                    {[
                        { label: 'Ставка', value: formatPrice(upgrade.bet_amount) },
                        { label: 'Цель', value: formatPrice(upgrade.target_price) },
                        { label: 'Шанс', value: upgrade.chance.toFixed(2) + '%' },
                        { label: 'Ролл', value: upgrade.roll_value.toFixed(6) },
                    ].map((item) => (
                        <div
                            key={item.label}
                            className="rounded-xl border border-border bg-card p-4 text-center"
                        >
                            <p className="text-xs font-medium text-[#888888] mb-1">{item.label}</p>
                            <p className="text-lg font-bold">{item.value}</p>
                        </div>
                    ))}
                </div>

                {/* Seeds */}
                <div className="rounded-xl border border-border bg-card p-6 space-y-4">
                    <h2 className="text-lg font-semibold">Сиды</h2>

                    {[
                        { label: 'Client Seed', value: upgrade.client_seed },
                        { label: 'Server Seed', value: upgrade.server_seed },
                        { label: 'Server Seed Hash', value: upgrade.server_seed_hash },
                        { label: 'Nonce', value: String(upgrade.nonce) },
                    ].map((item) => (
                        <div key={item.label}>
                            <p className="text-xs font-medium text-[#888888] mb-1">{item.label}</p>
                            <p className="rounded-lg bg-[#0a0a0a] border border-border px-4 py-2.5 text-sm font-mono break-all">
                                {item.value}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
