import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

interface ErrorPageProps {
    status: number;
}

export default function Error({ status }: ErrorPageProps) {
    const { t } = useTranslation();
    const message = t(`errors.${status}`, { defaultValue: t('common.error_generic') });

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
                    {t('common.to_home')}
                </Link>
            </div>
        </>
    );
}
