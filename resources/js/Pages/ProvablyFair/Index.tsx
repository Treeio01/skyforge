import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { type PageProps } from '@/types';
import { FormEvent } from 'react';

interface SeedPair {
    client_seed: string;
    server_seed_hash: string;
    nonce: number;
}

interface ProvablyFairPageProps extends Record<string, unknown> {
    seedPair: SeedPair;
}

export default function Index() {
    const { seedPair } = usePage<PageProps<ProvablyFairPageProps>>().props;

    const { data, setData, post, processing, errors } = useForm({
        client_seed: '',
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        post(route('provably-fair.client-seed'));
    }

    return (
        <AppLayout>
            <Head title="Честная игра" />

            <div className="max-w-2xl mx-auto px-4 py-10 space-y-8">
                <h1 className="text-2xl font-bold">Честная игра</h1>

                {/* Current Seed Pair */}
                <div className="rounded-xl border border-border bg-card p-6 space-y-4">
                    <h2 className="text-lg font-semibold">Текущая пара</h2>

                    <div>
                        <p className="text-xs font-medium text-[#888888] mb-1">Client Seed</p>
                        <p className="rounded-lg bg-[#0a0a0a] border border-border px-4 py-2.5 text-sm font-mono break-all">
                            {seedPair.client_seed}
                        </p>
                    </div>

                    <div>
                        <p className="text-xs font-medium text-[#888888] mb-1">Server Seed Hash</p>
                        <p className="rounded-lg bg-[#0a0a0a] border border-border px-4 py-2.5 text-sm font-mono break-all">
                            {seedPair.server_seed_hash}
                        </p>
                    </div>

                    <div>
                        <p className="text-xs font-medium text-[#888888] mb-1">Nonce</p>
                        <p className="rounded-lg bg-[#0a0a0a] border border-border px-4 py-2.5 text-sm font-mono">
                            {seedPair.nonce}
                        </p>
                    </div>
                </div>

                {/* Update Client Seed */}
                <div className="rounded-xl border border-border bg-card p-6">
                    <h2 className="text-lg font-semibold mb-4">Обновить Client Seed</h2>
                    <p className="text-sm text-[#888888] mb-4">
                        При смене клиентского сида будет раскрыт текущий серверный сид и создана новая пара.
                    </p>
                    <form onSubmit={handleSubmit} className="flex gap-3">
                        <input
                            type="text"
                            value={data.client_seed}
                            onChange={(e) => setData('client_seed', e.target.value)}
                            placeholder="Введите новый client seed"
                            className="flex-1 rounded-lg border border-border bg-[#0a0a0a] px-4 py-2.5 text-sm text-[#f5f5f5] placeholder-[#888888] focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none"
                        />
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-[#0a0a0a] hover:bg-accent-hover transition-colors disabled:opacity-50"
                        >
                            Обновить
                        </button>
                    </form>
                    {errors.client_seed && (
                        <p className="mt-2 text-sm text-[#ef4444]">{errors.client_seed}</p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
