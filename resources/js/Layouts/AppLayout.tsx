import { ReactNode } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { usePage } from '@inertiajs/react';
import Header from '@/Components/Layout/Header';
import AppFooter from '@/Components/Layout/AppFooter';
import LiveFeed from '@/Components/Upgrade/LiveFeed';
import LoginModal from '@/Components/UI/LoginModal';

interface AppLayoutProps {
    children: ReactNode;
}

export default function AppLayout({ children }: AppLayoutProps) {
    const url = usePage().url;

    return (
        <div className="flex items-stretch min-h-screen 1024:flex-row flex-col">
            <LiveFeed />
            <div className="flex flex-col w-full flex-1 min-h-0 overflow-y-auto relative">
                <Header />
                <AnimatePresence mode="wait" initial={false}>
                    <motion.main
                        key={url}
                        initial={{ opacity: 0, y: 18 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        transition={{ duration: 0.32, ease: [0.16, 1, 0.3, 1] }}
                        className="flex flex-col w-full"
                    >
                        {children}
                    </motion.main>
                </AnimatePresence>
                <AppFooter />
            </div>
            <LoginModal />
        </div>
    );
}
