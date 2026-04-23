import { CSSProperties, useEffect, useRef, useState } from 'react';
import CrateSkinOverlay from './CrateSkinOverlay';
import DefuseOverlay, { DefuseOutcome } from './DefuseOverlay';
import { SkinEntry } from './SkinCard';
import { QuickMultiplier, Stage } from './UpgradeBlock';
import { GameHud, GoButton, MultiplierPanel } from './VideoOverlays';
import { DeviceKind, UPGRADE_VIDEOS, UpgradeState } from './upgradeVideos';

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
                <GameHud
                    device={device}
                    chance={chance}
                    multiplier={multiplier}
                />
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
                <GoButton
                    device={device}
                    canStart={canStart}
                    onGo={onGo}
                />
            )}

            {showGameHud && (
                <MultiplierPanel
                    activeQuick={activeQuick}
                    isIdle={isIdle}
                    onMultiplierChange={onMultiplierChange}
                />
            )}

            <div className="absolute bottom-[-2px] left-0 w-full h-1/2 bg-linear-to-b from-transparent via-[#090B10]/85 via-75% to-[#090B10] pointer-events-none z-[55]" />
        </div>
    );
}
