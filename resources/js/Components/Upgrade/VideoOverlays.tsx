import { QuickMultiplier } from './UpgradeBlock';
import { DeviceKind } from './upgradeVideos';

const MULTIPLIER_OPTIONS: Array<{ label: string; value: QuickMultiplier }> = [
    { label: 'x2', value: 2 },
    { label: 'x3', value: 3 },
    { label: 'x5', value: 5 },
    { label: 'x10', value: 10 },
];

interface GameHudProps {
    device: DeviceKind;
    chance: number;
    multiplier: number;
}

export function GameHud({ device, chance, multiplier }: GameHudProps) {
    return (
        <div
            className="absolute z-[60] flex justify-between items-center"
            style={
                device === 'pc'
                    ? {
                          top: '25.7%',
                          left: '52.1%',
                          width: '12%',
                          height: '5%',
                          transform: 'translate(-50%, -50%)',
                      }
                    : {
                          top: '14%',
                          left: '52.1%',
                          width: '12%',
                          height: '5%',
                          transform: 'translate(-50%, -50%)',
                      }
            }
        >
            <span className="font-antonio text-[2cqw] leading-[100%] text-black/36">
                {chance.toFixed(1)}%
            </span>
            <span className="font-antonio text-[2cqw] leading-[100%] text-black/36">
                x{multiplier < 10 ? multiplier.toFixed(2) : Math.round(multiplier)}
            </span>
        </div>
    );
}

interface GoButtonProps {
    device: DeviceKind;
    canStart: boolean;
    onGo: () => void;
}

export function GoButton({ device, canStart, onGo }: GoButtonProps) {
    return (
        <button
            type="button"
            onClick={onGo}
            disabled={!canStart}
            className={`absolute z-[60] ${
                canStart ? 'cursor-pointer' : 'cursor-not-allowed opacity-40'
            }`}
            style={
                device === 'pc'
                    ? {
                          top: '62%',
                          left: '50.5%',
                          width: '9%',
                          height: '25%',
                          transform: 'translate(-50%, -50%)',
                      }
                    : {
                          top: '30%',
                          left: '50.5%',
                          width: '9%',
                          height: '13%',
                          transform: 'translate(-50%, -50%)',
                      }
            }
        >
            <span className="font-gotham-cond font-bold text-white text-[min(2vw,22px)] leading-none">
                GO
            </span>
        </button>
    );
}

interface MultiplierPanelProps {
    activeQuick: QuickMultiplier | null;
    isIdle: boolean;
    onMultiplierChange: (m: QuickMultiplier) => void;
}

export function MultiplierPanel({
    activeQuick,
    isIdle,
    onMultiplierChange,
}: MultiplierPanelProps) {
    return (
        <div
            className="absolute z-[60] hidden 1024:flex"
            style={{
                top: '56%',
                left: '80%',
                width: '22%',
                gap: '2%',
                transform:
                    'translate(-50%, -50%) rotate(-3deg) rotateX(10deg) rotateY(11deg)',
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
    );
}
