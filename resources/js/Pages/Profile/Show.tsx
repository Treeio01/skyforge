import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { type PageProps, type User } from '@/types';
import { FormEvent } from 'react';

interface ProfilePageProps extends Record<string, unknown> {
    profileUser: User;
    stats: {
        total_deposited: number;
        total_withdrawn: number;
        total_upgraded: number;
        total_won: number;
    };
}

function formatPrice(kopecks: number): string {
    return (kopecks / 100).toLocaleString('ru-RU', { minimumFractionDigits: 2 }) + ' \u20BD';
}

export default function Show() {
    const { profileUser, stats } = usePage<PageProps<ProfilePageProps>>().props;

    const { data, setData, put, processing, errors } = useForm({
        trade_url: profileUser.trade_url ?? '',
    });

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        put(route('profile.trade-url'));
    }

    return (
        <AppLayout>
            <Head title="Профиль" />

            <div className="max-w-3xl mx-auto px-4 py-10 space-y-8">
                {/* User Card */}
                <div className="rounded-xl border border-border bg-card p-6 flex items-center gap-5">
                    {profileUser.avatar_url ? (
                        <img
                            src={profileUser.avatar_url}
                            alt={profileUser.username}
                            className="w-16 h-16 rounded-full"
                        />
                    ) : (
                        <div className="w-16 h-16 rounded-full bg-border flex items-center justify-center text-2xl font-bold text-[#888888]">
                            {profileUser.username.charAt(0).toUpperCase()}
                        </div>
                    )}
                    <div>
                        <h1 className="text-xl font-bold">{profileUser.username}</h1>
                        <p className="text-sm text-[#888888]">Steam ID: {profileUser.steam_id}</p>
                    </div>
                    <Link
                        href={route('profile.history')}
                        className="ml-auto text-sm font-medium text-accent hover:text-accent-hover transition-colors"
                    >
                        История
                    </Link>
                </div>

                {/* Trade URL Form */}
                <div className="rounded-xl border border-border bg-card p-6">
                    <h2 className="text-lg font-semibold mb-4">Trade URL</h2>
                    <form onSubmit={handleSubmit} className="flex gap-3">
                        <input
                            type="url"
                            value={data.trade_url}
                            onChange={(e) => setData('trade_url', e.target.value)}
                            placeholder="https://steamcommunity.com/tradeoffer/new/?partner=..."
                            className="flex-1 rounded-lg border border-border bg-[#0a0a0a] px-4 py-2.5 text-sm text-[#f5f5f5] placeholder-[#888888] focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none"
                        />
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-[#0a0a0a] hover:bg-accent-hover transition-colors disabled:opacity-50"
                        >
                            Сохранить
                        </button>
                    </form>
                    {errors.trade_url && (
                        <p className="mt-2 text-sm text-[#ef4444]">{errors.trade_url}</p>
                    )}
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {[
                        { label: 'Пополнено', value: stats.total_deposited },
                        { label: 'Выведено', value: stats.total_withdrawn },
                        { label: 'Поставлено', value: stats.total_upgraded },
                        { label: 'Выиграно', value: stats.total_won },
                    ].map((stat) => (
                        <div
                            key={stat.label}
                            className="rounded-xl border border-border bg-card p-5 text-center"
                        >
                            <p className="text-xs font-medium text-[#888888] mb-1">{stat.label}</p>
                            <p className="text-lg font-bold">{formatPrice(stat.value)}</p>
                        </div>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
