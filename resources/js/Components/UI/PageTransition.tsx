import { motion, AnimatePresence } from 'framer-motion';
import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

const easeOutQuint = [0.16, 1, 0.3, 1] as const;
const easeInOutQuint = [0.83, 0, 0.17, 1] as const;

type Phase = 'idle' | 'covering' | 'holding' | 'revealing';

const COVER_MS = 380;
const HOLD_MIN_MS = 220;
const REVEAL_MS = 500;

export default function PageTransition({ children }: { children: React.ReactNode }) {
    const [phase, setPhase] = useState<Phase>('idle');
    const startedAtRef = useRef<number>(0);
    const finishTimerRef = useRef<ReturnType<typeof setTimeout>>();

    useEffect(() => {
        const removeStart = router.on('start', () => {
            startedAtRef.current = performance.now();
            setPhase('covering');
            // After cover completes, switch to holding
            setTimeout(() => {
                setPhase((p) => (p === 'covering' ? 'holding' : p));
            }, COVER_MS);
        });

        const removeFinish = router.on('finish', () => {
            const elapsed = performance.now() - startedAtRef.current;
            const minTotal = COVER_MS + HOLD_MIN_MS;
            const wait = Math.max(0, minTotal - elapsed);

            clearTimeout(finishTimerRef.current);
            finishTimerRef.current = setTimeout(() => {
                setPhase('revealing');
                setTimeout(() => setPhase('idle'), REVEAL_MS);
            }, wait);
        });

        return () => {
            removeStart();
            removeFinish();
            clearTimeout(finishTimerRef.current);
        };
    }, []);

    const visible = phase !== 'idle';

    return (
        <>
            {children}

            <AnimatePresence>
                {visible && (
                    <motion.div
                        key="curtain"
                        className="pointer-events-none fixed inset-0 z-[9999] overflow-hidden"
                        initial={{ opacity: 1 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.18 }}
                    >
                        {/* Верхняя половина занавеса */}
                        <motion.div
                            className="absolute top-0 left-0 right-0 h-[55%]"
                            style={{
                                background:
                                    'linear-gradient(180deg, #050810 0%, #0A1020 60%, #0E1830 100%)',
                                boxShadow: '0 12px 40px rgba(78,137,255,0.18)',
                            }}
                            initial={{ y: '-100%' }}
                            animate={{
                                y: phase === 'revealing' ? '-100%' : '0%',
                            }}
                            transition={{
                                duration: phase === 'revealing' ? REVEAL_MS / 1000 : COVER_MS / 1000,
                                ease: phase === 'revealing' ? easeInOutQuint : easeOutQuint,
                            }}
                        />

                        {/* Нижняя половина занавеса */}
                        <motion.div
                            className="absolute bottom-0 left-0 right-0 h-[55%]"
                            style={{
                                background:
                                    'linear-gradient(0deg, #050810 0%, #0A1020 60%, #0E1830 100%)',
                                boxShadow: '0 -12px 40px rgba(78,137,255,0.18)',
                            }}
                            initial={{ y: '100%' }}
                            animate={{
                                y: phase === 'revealing' ? '100%' : '0%',
                            }}
                            transition={{
                                duration: phase === 'revealing' ? REVEAL_MS / 1000 : COVER_MS / 1000,
                                ease: phase === 'revealing' ? easeInOutQuint : easeOutQuint,
                            }}
                        />

                        {/* Центральная горизонтальная линия (брендовый акцент) */}
                        <motion.div
                            className="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-[2px] origin-center"
                            style={{
                                background:
                                    'linear-gradient(90deg, transparent 0%, #4E89FF 30%, #4E89FF 70%, transparent 100%)',
                                boxShadow: '0 0 24px rgba(78,137,255,0.7)',
                            }}
                            initial={{ scaleX: 0, opacity: 0 }}
                            animate={{
                                scaleX: phase === 'revealing' ? 1.2 : 1,
                                opacity: phase === 'revealing' ? 0 : 1,
                            }}
                            transition={{
                                duration: phase === 'revealing' ? 0.35 : 0.45,
                                ease: easeOutQuint,
                                delay: phase === 'covering' || phase === 'holding' ? COVER_MS / 1000 / 2 : 0,
                            }}
                        />

                        {/* Лого по центру */}
                        <motion.div
                            className="absolute inset-0 flex items-center justify-center"
                            initial={{ opacity: 0, scale: 0.85 }}
                            animate={{
                                opacity: phase === 'covering' || phase === 'holding' ? 1 : 0,
                                scale: phase === 'covering' || phase === 'holding' ? 1 : 0.92,
                            }}
                            transition={{
                                duration: 0.35,
                                ease: easeOutQuint,
                                delay: phase === 'covering' ? 0.18 : 0,
                            }}
                        >
                            <div className="relative">
                                <div
                                    className="absolute inset-0 -m-8 rounded-full blur-2xl"
                                    style={{
                                        background:
                                            'radial-gradient(circle, rgba(78,137,255,0.3) 0%, transparent 70%)',
                                    }}
                                />
                                <img
                                    src="/assets/img/logo.png"
                                    alt=""
                                    className="relative h-12 w-auto object-contain"
                                />
                            </div>
                        </motion.div>
                    </motion.div>
                )}
            </AnimatePresence>
        </>
    );
}
