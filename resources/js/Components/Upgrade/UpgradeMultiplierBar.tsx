import { MULTIPLIERS } from "./useUpgrade";
import { QuickMultiplier } from "./upgradeCalculations";

interface UpgradeMultiplierBarProps {
    activeQuick: QuickMultiplier | null;
    onMultiplierChange: (m: QuickMultiplier) => void;
}

export default function UpgradeMultiplierBar({
    activeQuick,
    onMultiplierChange,
}: UpgradeMultiplierBarProps) {
    return (
        <div className="absolute z-[100] right-3 top-[35%] -translate-y-1/2 flex flex-col items-center gap-1 1024:hidden">
            {MULTIPLIERS.map((m) => {
                const active = activeQuick === m;
                return (
                    <button
                        key={m}
                        type="button"
                        onClick={() => onMultiplierChange(m)}
                        className={`w-12 h-12 flex items-center justify-center rounded-[8px] cursor-pointer ${active ? 'bg-white/20' : 'bg-white/6'}`}
                    >
                        <span className={`font-gotham font-medium text-[12px] leading-[104%] ${active ? 'text-white' : 'text-white/29'}`}>x{m}</span>
                    </button>
                );
            })}
        </div>
    );
}
