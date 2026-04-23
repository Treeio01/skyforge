import { motion, AnimatePresence } from 'framer-motion';
import { router } from '@inertiajs/react';
import { ReactNode, useEffect, useRef, useState } from 'react';

interface PageTransitionProps {
    children: ReactNode;
}

export default function PageTransition({ children }: PageTransitionProps) {
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
            }, 100);
        });

        const removeProgress = router.on('progress', (event) => {
            if (event.detail.progress?.percentage) {
                setProgress(Math.max(70, event.detail.progress.percentage));
            }
        });

        const removeFinish = router.on('finish', () => {
            clearTimeout(timerRef.current);
            setProgress(100);
            timerRef.current = setTimeout(() => {
                setVisible(false);
                setProgress(0);
            }, 400);
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
            {/* Progress bar */}
            <AnimatePresence>
                {visible && (
                    <motion.div
                        className="fixed top-0 left-0 h-[2px] z-[99999] bg-brand"
                        initial={{ width: '0%', opacity: 1 }}
                        animate={{ width: `${progress}%`, opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.4, ease: 'easeOut' }}
                    />
                )}
            </AnimatePresence>

            {/* Page content */}
            {children}
        </>
    );
}
