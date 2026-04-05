import { Head, Link } from '@inertiajs/react';

const statusMessages: Record<number, string> = {
    403: 'Доступ запрещён',
    404: 'Страница не найдена',
    419: 'Сессия истекла',
    500: 'Ошибка сервера',
    503: 'Технические работы',
};

interface ErrorPageProps {
    status: number;
}

export default function Error({ status }: ErrorPageProps) {
    const message = statusMessages[status] ?? 'Произошла ошибка';

    return (
        <>
            <Head title={`${status} — ${message}`} />

            <div className="flex min-h-screen flex-col items-center justify-center bg-[#0a0a0a] text-[#f5f5f5] px-4">
                <h1 className="text-8xl font-extrabold tracking-tight">{status}</h1>
                <p className="mt-4 text-xl text-[#888888]">{message}</p>
                <Link
                    href="/"
                    className="mt-8 inline-flex items-center rounded-lg bg-accent px-6 py-3 text-base font-bold text-[#0a0a0a] hover:bg-accent-hover transition-colors"
                >
                    На главную
                </Link>
            </div>
        </>
    );
}
