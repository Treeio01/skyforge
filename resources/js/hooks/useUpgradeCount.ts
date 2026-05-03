import { usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import type { PageProps } from '@/types';
import { ensureEcho } from '@/realtime/ensureEcho';

const ANIMATION_MS = 600;

function easeOutQuad(t: number): number {
    return 1 - (1 - t) * (1 - t);
}

export function useUpgradeCount(): number {
    const { stats } = usePage<PageProps>().props;
    const initial = stats?.total_upgrades ?? 0;

    const [display, setDisplay] = useState(initial);
    const displayRef = useRef(initial);
    const fromRef = useRef(initial);
    const targetRef = useRef(initial);
    const animationStartRef = useRef<number | null>(null);
    const rafRef = useRef<number | null>(null);

    useEffect(() => {
        setDisplay(initial);
        displayRef.current = initial;
        fromRef.current = initial;
        targetRef.current = initial;
    }, [initial]);

    useEffect(() => {
        displayRef.current = display;
    }, [display]);

    useEffect(() => {
        if (typeof window === 'undefined') {
            return undefined;
        }

        let cancelled = false;

        let stopListening: (() => void) | undefined;

        void ensureEcho().then((echo) => {
            if (cancelled || !echo) {
                return;
            }

            function tick() {
                const now = performance.now();
                const elapsed = animationStartRef.current ? now - animationStartRef.current : 0;
                const progress = Math.min(1, elapsed / ANIMATION_MS);
                const eased = easeOutQuad(progress);
                const value = Math.round(
                    fromRef.current + (targetRef.current - fromRef.current) * eased,
                );

                setDisplay(value);

                if (progress < 1) {
                    rafRef.current = requestAnimationFrame(tick);
                }
            }

            const channel = echo.channel('stats');

            channel.listen('.upgrades.updated', ({ total_upgrades }: { total_upgrades: number }) => {
                fromRef.current = displayRef.current;
                targetRef.current = total_upgrades;
                animationStartRef.current = performance.now();

                if (rafRef.current !== null) {
                    cancelAnimationFrame(rafRef.current);
                }

                rafRef.current = requestAnimationFrame(tick);
            });

            stopListening = () => channel.stopListening('.upgrades.updated');
        });

        return () => {
            cancelled = true;
            stopListening?.();

            if (rafRef.current !== null) {
                cancelAnimationFrame(rafRef.current);
            }
        };
    }, []);

    return display;
}
