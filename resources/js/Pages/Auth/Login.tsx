import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Login() {
    return (
        <AppLayout>
            <Head title="Вход" />

            <div className="flex items-center justify-center min-h-[calc(100vh-3.5rem)] px-4">
                <div className="w-full max-w-sm rounded-xl border border-border bg-card p-8 text-center">
                    <h1 className="text-2xl font-bold mb-2">Вход</h1>
                    <p className="text-sm text-[#888888] mb-8">
                        Для входа используйте аккаунт Steam
                    </p>

                    <a
                        href={route('auth.steam')}
                        className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-accent px-6 py-3 text-base font-bold text-[#0a0a0a] hover:bg-accent-hover transition-colors"
                    >
                        Войти через Steam
                    </a>
                </div>
            </div>
        </AppLayout>
    );
}
