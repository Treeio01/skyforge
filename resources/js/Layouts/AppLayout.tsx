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

const easeOutQuint = [0.16, 1, 0.3, 1] as const;
const easeInQuint = [0.64, 0, 0.78, 0] as const;

export default function AppLayout({ children }: AppLayoutProps) {
    const url = usePage().url;

    return (
        <div className="flex items-stretch min-h-screen 1024:flex-row flex-col">
            <LiveFeed />
            <div className="flex flex-col w-full flex-1 min-h-0 overflow-y-auto relative">
                <Header />
                <AnimatePresence mode="wait">
                    <motion.main
                        key={url}
                        initial={{
                            opacity: 0,
                            y: 48,
                            scale: 0.94,
                            filter: 'blur(12px)',
                        }}
                        animate={{
                            opacity: 1,
                            y: 0,
                            scale: 1,
                            filter: 'blur(0px)',
                            transition: { duration: 0.55, ease: easeOutQuint },
                        }}
                        exit={{
                            opacity: 0,
                            y: -24,
                            scale: 0.98,
                            filter: 'blur(8px)',
                            transition: { duration: 0.28, ease: easeInQuint },
                        }}
                        className="flex flex-col w-full will-change-transform"
                    >
                        {children}
                    </motion.main>
                </AnimatePresence>
                <AppFooter />
            </div>
            <LoginModal />
            <PageSweep />
        </div>
    );
}

/**
 * Brand-color sweep overlay that flashes across the screen on every route change.
 * Visible cinematic accent without blocking interaction.
 */
function PageSweep() {
    const url = usePage().url;

    return (
        <AnimatePresence>
            <motion.div
                key={`sweep-${url}`}
                className="pointer-events-none fixed inset-0 z-[9999]"
                initial={{ opacity: 0 }}
                animate={{ opacity: 0 }}
                exit={{ opacity: 0 }}
            >
                <motion.div
                    className="absolute top-0 bottom-0 w-[40%]"
                    style={{
                        background:
                            'linear-gradient(90deg, transparent 0%, rgba(78,137,255,0) 10%, rgba(78,137,255,0.18) 50%, rgba(78,137,255,0) 90%, transparent 100%)',
                        filter: 'blur(40px)',
                    }}
                    initial={{ left: '-50%' }}
                    animate={{ left: '110%' }}
                    transition={{ duration: 0.7, ease: easeOutQuint }}
                />
            </motion.div>
        </AnimatePresence>
    );
}
