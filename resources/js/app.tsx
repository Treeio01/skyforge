import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import ErrorBoundary from '@/Components/ErrorBoundary';
import { ToastProvider } from '@/Components/UI/Toast';
import PageTransition from '@/Components/UI/PageTransition';

const appName = import.meta.env.VITE_APP_NAME || 'GROWSKINS';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx');

        return resolvePageComponent(`./Pages/${name}.tsx`, pages);
    },
    setup({ el, App, props }) {
        const root = createRoot(el);

        // Hide initial loader
        const loader = document.getElementById('page-loader');
        if (loader) loader.classList.add('hidden');

        root.render(
            <ErrorBoundary>
                <ToastProvider>
                    <PageTransition>
                        <App {...props} />
                    </PageTransition>
                </ToastProvider>
            </ErrorBoundary>,
        );
    },
    progress: false, // Отключаем дефолтный прогресс-бар — у нас свой
});

// Навигационный прелоадер
let navigating = false;

router.on('start', () => {
    navigating = true;
    setTimeout(() => {
        if (navigating) {
            document.body.classList.add('navigating');
        }
    }, 150); // Показывать только если загрузка > 150ms
});

router.on('finish', () => {
    navigating = false;
    document.body.classList.remove('navigating');
});
