import { CSSProperties, useEffect, useRef, useState } from 'react';

export type DefuseOutcome = 'success' | 'fail';

interface DefuseOverlayProps {
    outcome: DefuseOutcome;
    durationSec?: number;
    name?: string;
    className?: string;
    style?: CSSProperties;
}

const RING_COLOR = '#FEBB48';

function formatTime(ms: number): string {
    const clamped = Math.max(0, ms);
    const seconds = Math.floor(clamped / 1000);
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    const millis = Math.floor(clamped % 1000);
    return `${String(mins).padStart(2, '0')} : ${String(secs).padStart(2, '0')}.${String(
        millis,
    ).padStart(3, '0')}`;
}

export default function DefuseOverlay({
    outcome,
    durationSec = 5,
    name = 'Name',
    className = '',
    style,
}: DefuseOverlayProps) {
    const ringRef = useRef<SVGCircleElement>(null);
    const timerRef = useRef<HTMLSpanElement>(null);
    const [hidden, setHidden] = useState(false);

    useEffect(() => {
        const ring = ringRef.current;
        const timerEl = timerRef.current;
        if (!ring || !timerEl) return;

        const cycleMs = durationSec * 1000;
        const start = performance.now();
        let raf = 0;

        const tick = (now: number) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / cycleMs, 1);
            ring.style.strokeDashoffset = String(100 - progress * 100);
            const remaining = Math.max(0, cycleMs - elapsed);
            timerEl.textContent = formatTime(remaining);

            if (progress >= 1) {
                if (outcome === 'success') {
                    setHidden(true);
                    return;
                }
                // fail: остаёмся на 100%, но раз дошло — дальше крутить нечего.
                return;
            }

            raf = requestAnimationFrame(tick);
        };
        raf = requestAnimationFrame(tick);
        return () => cancelAnimationFrame(raf);
    }, [outcome, durationSec]);

    if (hidden) return null;

    return (
        <div
            className={`flex items-stretch rounded-[14px] overflow-hidden ${className}`}
            style={style}
        >
            <div className="w-[7px]" style={{ backgroundColor: RING_COLOR }} />
            <div
                className="flex py-[17px] px-[20px] md:py-[25px] md:px-[87px] gap-[47px] md:gap-[70px] items-center backdrop-blur-[25px]"
                style={{
                    background:
                        'linear-gradient(90deg, #906A46 0%, rgba(69, 64, 62, 0.66) 49.52%, #8C6548 100%)',
                }}
            >
                <div className="relative w-[60px] h-[60px]">
                    <svg
                        className="w-full h-full -rotate-90"
                        viewBox="0 0 100 100"
                    >
                        <circle
                            cx="50"
                            cy="50"
                            r="45"
                            fill="none"
                            stroke="rgba(255,255,255,0.14)"
                            strokeWidth="10"
                        />
                        <circle
                            ref={ringRef}
                            cx="50"
                            cy="50"
                            r="45"
                            fill="none"
                            stroke={RING_COLOR}
                            strokeWidth="10"
                            strokeLinecap="round"
                            pathLength="100"
                            strokeDasharray="100 100"
                            strokeDashoffset="100"
                        />
                    </svg>
                </div>
                <div className="flex flex-col h-full justify-between items-end gap-4">
                    <span className="text-white font-sf-display font-bold text-[9px] 1024:text-[14px] leading-[100%]">
                        {outcome === 'success'
                            ? `${name} defused the bomb.`
                            : `${name} is defusing the bomb.`}
                    </span>
                    <span
                        ref={timerRef}
                        className="text-white font-sf-display font-medium text-[9px] 1024:text-[14px] leading-[100%] tabular-nums"
                    >
                        {formatTime(durationSec * 1000)}
                    </span>
                </div>
            </div>
            <div className="w-[7px]" style={{ backgroundColor: RING_COLOR }} />
        </div>
    );
}
