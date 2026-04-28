import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { AnimatePresence, motion } from 'framer-motion';
import { createElement } from 'react';
import { createRoot } from 'react-dom/client';
import ErrorBoundary from '@/Components/ErrorBoundary';
import { ToastProvider } from '@/Components/UI/Toast';
import PageTransition from '@/Components/UI/PageTransition';

const appName = import.meta.env.VITE_APP_NAME || 'GROWSKINS';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx');

        return resolvePageComponent(`./Pages/${name}.tsx`, pages);
    },
    setup({ el, App, props }) {
        const root = createRoot(el);

        // Hide initial loader
        const loader = document.getElementById('page-loader');
        if (loader) loader.classList.add('hidden');

        const renderInertia = createElement(App, {
            ...props,
            children: ({ Component, props: pageProps, key }: { Component: React.ComponentType<Record<string, unknown>>; props: Record<string, unknown>; key: string }) => (
                <AnimatePresence mode="wait" initial={false}>
                    <motion.div
                        key={key}
                        initial={{ opacity: 0, y: 16 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        transition={{ duration: 0.32, ease: [0.16, 1, 0.3, 1] }}
                    >
                        <Component {...pageProps} />
                    </motion.div>
                </AnimatePresence>
            ),
        });

        root.render(
            <ErrorBoundary>
                <ToastProvider>
                    <PageTransition>{renderInertia}</PageTransition>
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
