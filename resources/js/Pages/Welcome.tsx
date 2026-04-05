import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { type PageProps } from '@/types';

export default function Welcome() {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;

    return (
        <AppLayout>
            <Head title="Главная" />

            <div className="flex flex-col items-center justify-center min-h-[calc(100vh-3.5rem)] px-4">
                <h1 className="text-5xl md:text-7xl font-extrabold tracking-tight text-center">
                    CS2 Skin <span className="text-accent">Upgrade</span>
                </h1>
                <p className="mt-4 text-lg md:text-xl text-[#888888] text-center max-w-md">
                    Апгрейд скинов CS2
                </p>

                <div className="mt-10">
                    {user ? (
                        <Link
                            href={route('deposit.create')}
                            className="inline-flex items-center rounded-lg bg-accent px-8 py-3 text-base font-bold text-[#0a0a0a] hover:bg-accent-hover transition-colors"
                        >
                            Играть
                        </Link>
                    ) : (
                        <a
                            href={route('auth.steam')}
                            className="inline-flex items-center rounded-lg bg-accent px-8 py-3 text-base font-bold text-[#0a0a0a] hover:bg-accent-hover transition-colors"
                        >
                            Войти через Steam
                        </a>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
