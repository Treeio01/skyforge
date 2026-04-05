import { Link, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { PropsWithChildren, useEffect, useState } from 'react';

function formatPrice(kopecks: number): string {
    return (kopecks / 100).toLocaleString('ru-RU', { minimumFractionDigits: 2 }) + ' \u20BD';
}

export default function AppLayout({ children }: PropsWithChildren) {
    const { auth, flash } = usePage<PageProps>().props;
    const user = auth.user;

    const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

    useEffect(() => {
        if (flash.success) {
            setToast({ message: flash.success, type: 'success' });
        } else if (flash.error) {
            setToast({ message: flash.error, type: 'error' });
        }
    }, [flash]);

    useEffect(() => {
        if (!toast) {
            return;
        }
        const timer = setTimeout(() => setToast(null), 4000);
        return () => clearTimeout(timer);
    }, [toast]);

    return (
        <div className="min-h-screen bg-[#0a0a0a] text-[#f5f5f5]">
            {/* Navbar */}
            <nav className="fixed top-0 left-0 right-0 z-50 h-14 border-b border-border bg-card flex items-center px-4 lg:px-8">
                {/* Left: Logo */}
                <Link href={route('home')} className="text-accent font-extrabold text-xl tracking-tight mr-8">
                    SKYFORGE
                </Link>

                {/* Center: Nav links */}
                <div className="hidden md:flex items-center gap-6 text-sm font-medium text-[#888888]">
                    {user && (
                        <>
                            <Link
                                href={route('deposit.create')}
                                className="hover:text-[#f5f5f5] transition-colors"
                            >
                                Пополнить
                            </Link>
                            <Link
                                href={route('provably-fair')}
                                className="hover:text-[#f5f5f5] transition-colors"
                            >
                                Честная игра
                            </Link>
                        </>
                    )}
                </div>

                {/* Right: Balance + Avatar or Login */}
                <div className="ml-auto flex items-center gap-4">
                    {user ? (
                        <>
                            <div className="text-sm font-semibold text-accent">
                                {formatPrice(user.balance)}
                            </div>
                            <Link
                                href={route('profile')}
                                className="flex items-center gap-2 hover:opacity-80 transition-opacity"
                            >
                                {user.avatar_url ? (
                                    <img
                                        src={user.avatar_url}
                                        alt={user.username}
                                        className="w-8 h-8 rounded-full"
                                    />
                                ) : (
                                    <div className="w-8 h-8 rounded-full bg-border flex items-center justify-center text-xs font-bold text-[#888888]">
                                        {user.username.charAt(0).toUpperCase()}
                                    </div>
                                )}
                                <span className="hidden lg:inline text-sm font-medium">
                                    {user.username}
                                </span>
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="text-xs text-[#888888] hover:text-[#f5f5f5] transition-colors"
                            >
                                Выйти
                            </Link>
                        </>
                    ) : (
                        <a
                            href={route('auth.steam')}
                            className="inline-flex items-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-[#0a0a0a] hover:bg-accent-hover transition-colors"
                        >
                            Войти через Steam
                        </a>
                    )}
                </div>
            </nav>

            {/* Toast */}
            {toast && (
                <div
                    className={`fixed top-16 right-4 z-50 max-w-sm rounded-lg border px-4 py-3 text-sm font-medium shadow-lg transition-all ${
                        toast.type === 'success'
                            ? 'border-accent/30 bg-accent-dim text-accent'
                            : 'border-[#ef4444]/30 bg-[#ef4444]/10 text-[#ef4444]'
                    }`}
                >
                    {toast.message}
                </div>
            )}

            {/* Content */}
            <main className="pt-14">
                {children}
            </main>
        </div>
    );
}
