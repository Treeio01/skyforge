import { useEffect, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import { ensureEcho } from '@/realtime/ensureEcho';

const ANIMATION_MS = 600;

function easeOutQuad(t: number): number {
    return 1 - (1 - t) * (1 - t);
}

export function useOnlineCount(): number {
    const { stats } = usePage<PageProps>().props;
    const realCount = stats?.online_real ?? 0;
    const enabled = stats?.online_enabled ?? false;
    const fakeInitial = stats?.online_fake_initial ?? 0;

    const initial = realCount + (enabled ? fakeInitial : 0);

    const [display, setDisplay] = useState(initial);
    const fromRef = useRef(initial);
    const targetRef = useRef(initial);
    const animationStartRef = useRef<number | null>(null);
    const rafRef = useRef<number | null>(null);
    const realCountRef = useRef(realCount);
    const displayRef = useRef(display);

    useEffect(() => {
        realCountRef.current = realCount;
    }, [realCount]);

    useEffect(() => {
        displayRef.current = display;
    }, [display]);

    useEffect(() => {
        const nextInitial = realCount + (enabled ? fakeInitial : 0);

        setDisplay(nextInitial);
        fromRef.current = nextInitial;
        targetRef.current = nextInitial;
    }, [fakeInitial, realCount, enabled]);

    useEffect(() => {
        if (!enabled || typeof window === 'undefined') {
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

            channel.listen('.online.updated', ({ fake }: { fake: number }) => {
                const next = realCountRef.current + fake;
                fromRef.current = displayRef.current;
                targetRef.current = next;
                animationStartRef.current = performance.now();

                if (rafRef.current !== null) {
                    cancelAnimationFrame(rafRef.current);
                }

                rafRef.current = requestAnimationFrame(tick);
            });

            stopListening = () => channel.stopListening('.online.updated');
        });

        return () => {
            cancelled = true;
            stopListening?.();

            if (rafRef.current !== null) {
                cancelAnimationFrame(rafRef.current);
            }
        };
    }, [enabled]);

    return display;
}
