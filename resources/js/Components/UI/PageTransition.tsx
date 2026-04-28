import { motion, AnimatePresence } from 'framer-motion';
import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

/**
 * Тонкая бренд-полоса прогресса сверху на время Inertia-перехода.
 * Появляется только если переход дольше 120ms — короткие переходы не флэшим.
 */
export default function PageTransition({ children }: { children: React.ReactNode }) {
    const [progress, setProgress] = useState(0);
    const [visible, setVisible] = useState(false);
    const timerRef = useRef<ReturnType<typeof setTimeout>>();

    useEffect(() => {
        const removeStart = router.on('start', () => {
            clearTimeout(timerRef.current);
            setProgress(0);
            timerRef.current = setTimeout(() => {
                setVisible(true);
                setProgress(70);
            }, 120);
        });

        const removeProgress = router.on('progress', (event) => {
            const pct = event.detail.progress?.percentage;
            if (typeof pct === 'number') {
                setProgress(Math.max(70, pct));
            }
        });

        const removeFinish = router.on('finish', () => {
            clearTimeout(timerRef.current);
            setProgress(100);
            timerRef.current = setTimeout(() => {
                setVisible(false);
                setProgress(0);
            }, 280);
        });

        return () => {
            removeStart();
            removeProgress();
            removeFinish();
            clearTimeout(timerRef.current);
        };
    }, []);

    return (
        <>
            <AnimatePresence>
                {visible && (
                    <motion.div
                        className="fixed top-0 left-0 right-0 z-[9999] pointer-events-none"
                        initial={{ opacity: 1 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.25 }}
                    >
                        <motion.div
                            className="h-[2px]"
                            style={{
                                background:
                                    'linear-gradient(90deg, #4E89FF 0%, #6BA3FF 50%, #4E89FF 100%)',
                                boxShadow: '0 0 12px rgba(78,137,255,0.6)',
                            }}
                            initial={{ width: '0%' }}
                            animate={{ width: `${progress}%` }}
                            transition={{ duration: 0.35, ease: [0.16, 1, 0.3, 1] }}
                        />
                    </motion.div>
                )}
            </AnimatePresence>

            {children}
        </>
    );
}
