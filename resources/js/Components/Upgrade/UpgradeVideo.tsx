import { CSSProperties, useEffect, useRef, useState } from 'react';
import CrateSkinOverlay from './CrateSkinOverlay';
import DefuseOverlay, { DefuseOutcome } from './DefuseOverlay';
import { SkinEntry } from './SkinCard';
import { QuickMultiplier, Stage } from './UpgradeBlock';
import { DeviceKind, UPGRADE_VIDEOS, UpgradeState } from './upgradeVideos';

const MULTIPLIER_OPTIONS: Array<{ label: string; value: QuickMultiplier }> = [
    { label: 'x2', value: 2 },
    { label: 'x3', value: 3 },
    { label: 'x5', value: 5 },
    { label: 'x10', value: 10 },
];

const VIDEO_STATES: UpgradeState[] = [
    'idle',
    'playing',
    'playing_two',
    'won',
    'lose',
];

interface UpgradeVideoProps {
    state?: UpgradeState;
    device?: DeviceKind;
    className?: string;

    inventorySkin: SkinEntry | null;
    targetSkin: SkinEntry | null;
    multiplier: number | null;
    activeQuick: QuickMultiplier | null;
    onMultiplierChange: (m: QuickMultiplier) => void;
    chance: number;
    stage: Stage;
    outcome: DefuseOutcome | null;
    canStart: boolean;
    onGo: () => void;
    onRemoveInventory: () => void;
    onRemoveTarget: () => void;
    onVideoEnded: () => void;
    onClosingComplete: () => void;
}

export default function UpgradeVideo({
    state = 'idle',
    device = 'pc',
    className = '',
    inventorySkin,
    targetSkin,
    multiplier,
    activeQuick,
    onMultiplierChange,
    chance,
    stage,
    outcome,
    canStart,
    onGo,
    onRemoveInventory,
    onRemoveTarget,
    onVideoEnded,
    onClosingComplete,
}: UpgradeVideoProps) {
    const hasFullSetup = !!(inventorySkin && targetSkin && multiplier);
    const isIdle = stage === 'idle';
    const isResult = stage === 'result';
    const showCrateOverlays = isIdle;
    const showGameHud = !isResult;
    const showGo = isIdle;
    const showDefuse =
        (stage === 'playing' || stage === 'playing_two') && outcome !== null;

    // Ссылки на каждый <video> по state — чтобы не дёргать src и избежать flicker.
    const videoRefs = useRef<Partial<Record<UpgradeState, HTMLVideoElement>>>(
        {},
    );

    const [defuseDurationSec, setDefuseDurationSec] = useState(5);

    // Длительность дефьюза = длина play + playing_two (из metadata).
    useEffect(() => {
        const update = () => {
            const p = videoRefs.current.playing;
            const pt = videoRefs.current.playing_two;
            const pd = p?.duration;
            const ptd = pt?.duration;
            if (
                pd &&
                ptd &&
                isFinite(pd) &&
                isFinite(ptd) &&
                pd > 0 &&
                ptd > 0
            ) {
                setDefuseDurationSec(pd + ptd);
            }
        };
        update();
        const items: Array<[HTMLVideoElement, () => void]> = [];
        (['playing', 'playing_two'] as UpgradeState[]).forEach((s) => {
            const v = videoRefs.current[s];
            if (v) {
                v.addEventListener('loadedmetadata', update);
                items.push([v, update]);
            }
        });
        return () =>
            items.forEach(([v, h]) => v.removeEventListener('loadedmetadata', h));
    }, [device]);

    // Play/pause активного видео, seek в 0 при переключении.
    useEffect(() => {
        VIDEO_STATES.forEach((s) => {
            const v = videoRefs.current[s];
            if (!v) return;
            if (s === state) {
                if (s !== 'idle' && stage !== 'closing') {
                    v.currentTime = 0;
                }
                v.play().catch(() => {});
            } else {
                v.pause();
            }
        });
    }, [state, stage]);

    // Реверс idle-видео при closing.
    useEffect(() => {
        if (stage !== 'closing') return;
        const v = videoRefs.current.idle;
        if (!v) return;

        if (v.currentTime < 0.1) {
            v.currentTime = isFinite(v.duration) ? v.duration : 1;
        }

        let done = false;
        const finish = () => {
            if (done) return;
            done = true;
            try {
                v.pause();
                v.playbackRate = 1;
            } catch {
                /* noop */
            }
            onClosingComplete();
        };

        const dur = isFinite(v.duration) ? v.duration : 1;

        let usedNative = false;
        try {
            v.playbackRate = -1;
            v.play().catch(() => {});
            usedNative = true;
        } catch {
            usedNative = false;
        }

        if (usedNative) {
            const onTimeUpdate = () => {
                if (v.currentTime <= 0.1) finish();
            };
            v.addEventListener('timeupdate', onTimeUpdate);
            const safety = window.setTimeout(finish, dur * 1000 + 500);
            return () => {
                v.removeEventListener('timeupdate', onTimeUpdate);
                window.clearTimeout(safety);
                try {
                    v.playbackRate = 1;
                } catch {
                    /* noop */
                }
            };
        }

        v.pause();
        const fps = 25;
        const step = 1 / fps;
        const durationMs = Math.max(400, v.currentTime * 1000);

        const intervalId = window.setInterval(() => {
            if (v.currentTime <= step) {
                v.currentTime = 0;
                finish();
                return;
            }
            try {
                v.currentTime = v.currentTime - step;
            } catch {
                /* noop */
            }
        }, 1000 / fps);

        const safety = window.setTimeout(finish, durationMs + 500);
        return () => {
            window.clearInterval(intervalId);
            window.clearTimeout(safety);
        };
    }, [stage, onClosingComplete]);

    const wrapperStyle: CSSProperties = {
        containerType: 'inline-size',
        top: 0,
        left: '50%',
        transform: 'translateX(-50%)',
        width: '100%',
    };

    return (
        <div
            className={`absolute ${className}`}
            style={wrapperStyle}
        >
            {VIDEO_STATES.map((s) => {
                const isActive = s === state;
                return (
                    <video
                        key={s}
                        ref={(el) => {
                            if (el) videoRefs.current[s] = el;
                        }}
                        src={UPGRADE_VIDEOS[s][device]}
                        muted
                        playsInline
                        preload="auto"
                        onEnded={isActive ? onVideoEnded : undefined}
                        className={`block w-full h-auto ${
                            isActive
                                ? 'relative opacity-100'
                                : 'absolute inset-0 opacity-0 pointer-events-none'
                        }`}
                    />
                );
            })}

            {showCrateOverlays && inventorySkin && (
                <CrateSkinOverlay
                    price={inventorySkin.price}
                    weapon={inventorySkin.weapon}
                    name={inventorySkin.name}
                    image={inventorySkin.image}
                    onRemove={onRemoveInventory}
                    style={device === 'pc' ? {
                        top: '22%',
                        left: '9%',
                        width: '22%',
                        aspectRatio: '335 / 165',
                        transform: 'rotate(-1deg)',
                    } : {
                        top: '46%',
                        left: '28%',
                        width: '22%',
                        aspectRatio: '335 / 165',
                        transform: 'rotate(16deg)',
                    }}
                />
            )}

            {showCrateOverlays && targetSkin && (
                <CrateSkinOverlay
                    price={targetSkin.price}
                    weapon={targetSkin.weapon}
                    name={targetSkin.name}
                    image={targetSkin.image}
                    onRemove={onRemoveTarget}
                    style={device === 'pc' ? {
                        top: '24%',
                        left: '69%',
                        width: '22%',
                        aspectRatio: '335 / 160',
                        transform: 'rotate(-1deg)',
                    } : {
                        top: '47%',
                        left: '53.5%',
                        width: '22%',
                        aspectRatio: '335 / 160',
                        transform: 'rotate(-13deg)',
                    }}
                />
            )}

            {showGameHud && hasFullSetup && (
                <div
                    className="absolute z-[60] flex justify-between items-center"
                    style={device === 'pc' ? {
                        top: '25.7%',
                        left: '52.1%',
                        width: '12%',
                        height: '5%',
                        transform: 'translate(-50%, -50%)',
                    } : {
                        top: '14%',
                        left: '52.1%',
                        width: '12%',
                        height: '5%',
                        transform: 'translate(-50%, -50%)',
                    }}
                >
                    <span className="font-antonio text-[2cqw] leading-[100%] text-black/36">
                        {chance.toFixed(1)}%
                    </span>
                    <span className="font-antonio text-[2cqw] leading-[100%] text-black/36">
                        x{multiplier < 10 ? multiplier.toFixed(2) : Math.round(multiplier)}
                    </span>
                </div>
            )}

            {showDefuse && (
                <DefuseOverlay
                    key={outcome ?? 'none'}
                    outcome={outcome as DefuseOutcome}
                    durationSec={
                        outcome === 'success'
                            ? defuseDurationSec
                            : defuseDurationSec * 1.5
                    }
                    className="absolute z-[300]"
                    style={device === 'pc' ? {
                        top: '62%',
                        left: '50%',
                        transform: 'translate(-50%, -50%)',
                    } : {
                        top: '35%',
                        left: '50%',
                        transform: 'translate(-50%, -50%)',
                    }}
                />
            )}

            {showGo && (
                <button
                    type="button"
                    onClick={onGo}
                    disabled={!canStart}
                    className={`absolute z-[60] ${
                        canStart
                            ? 'cursor-pointer'
                            : 'cursor-not-allowed opacity-40'
                    }`}
                    style={device === 'pc' ? {
                        top: '62%',
                        left: '50.5%',
                        width: '9%',
                        height: '25%',
                        transform: 'translate(-50%, -50%)',
                    } : {
                        top: '30%',
                        left: '50.5%',
                        width: '9%',
                        height: '13%',
                        transform: 'translate(-50%, -50%)',
                    }}
                >
                    <span className="font-gotham-cond font-bold text-white text-[min(2vw,22px)] leading-none">
                        GO
                    </span>
                </button>
            )}

            {showGameHud && (
                <div
                    className="absolute z-[60] hidden 1024:flex"
                    style={{
                        top: '56%',
                        left: '80%',
                        width: '22%',
                        gap: '2%',
                        transform: 'translate(-50%, -50%) rotate(-3deg) rotateX(10deg) rotateY(11deg)',
                    }}
                >
                    {MULTIPLIER_OPTIONS.map(({ label, value }) => {
                        const active = activeQuick === value;
                        return (
                            <button
                                key={value}
                                type="button"
                                onClick={() => onMultiplierChange(value)}
                                disabled={!isIdle}
                                className={`flex-1 aspect-[4/3] flex items-center justify-center rounded-[8px] ${
                                    isIdle ? 'cursor-pointer' : 'cursor-default'
                                } ${active ? 'bg-white/20' : 'bg-white/6'}`}
                            >
                                <span
                                    className={`font-gotham font-medium leading-[104%] text-[1.1cqw] ${
                                        active ? 'text-white' : 'text-white/29'
                                    }`}
                                >
                                    {label}
                                </span>
                            </button>
                        );
                    })}
                </div>
            )}

            <div className="absolute bottom-[-2px] left-0 w-full h-1/2 bg-linear-to-b from-transparent via-[#090B10]/85 via-75% to-[#090B10] pointer-events-none z-[55]" />
        </div>
    );
}
