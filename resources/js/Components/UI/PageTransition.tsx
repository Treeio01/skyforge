import { router } from '@inertiajs/react';
import { ReactNode, useEffect, useState } from 'react';

interface PageTransitionProps {
    children: ReactNode;
}

export default function PageTransition({ children }: PageTransitionProps) {
    const [transitioning, setTransitioning] = useState(false);

    useEffect(() => {
        const removeStart = router.on('start', () => {
            setTransitioning(true);
        });

        const removeFinish = router.on('finish', () => {
            // Небольшая задержка чтобы fade-in отработал
            requestAnimationFrame(() => setTransitioning(false));
        });

        return () => {
            removeStart();
            removeFinish();
        };
    }, []);

    return (
        <>
            {/* Контент с fade */}
            <div
                className={`transition-opacity duration-300 ease-out ${
                    transitioning ? 'opacity-40' : 'opacity-100'
                }`}
            >
                {children}
            </div>

            {/* Верхняя полоса загрузки */}
            <div
                className={`fixed top-0 left-0 h-[2px] bg-[#4E89FF] z-[99999] transition-all ease-out ${
                    transitioning
                        ? 'w-[70%] duration-[2000ms]'
                        : 'w-full duration-300'
                }`}
                style={{ opacity: transitioning ? 1 : 0 }}
            />
        </>
    );
}
