import { ItemBackgroundLines } from '@/Components/Upgrade/SkinCard';
import { formatKopecks, mapRarityColor, parseSkinName } from '@/utils/skinHelpers';
import { useMemo } from 'react';

interface UpgradeEntry {
    id: number;
    target_skin_name: string;
    target_skin_image: string | null;
    target_skin_rarity_color: string | null;
    target_price: number;
    bet_amount: number;
    chance: number;
    result: 'win' | 'lose';
}

interface ProfileStatCardsProps {
    recentUpgrades: UpgradeEntry[];
}

export default function ProfileStatCards({ recentUpgrades }: ProfileStatCardsProps) {
    const wins = useMemo(
        () => recentUpgrades.filter((u) => u.result === 'win'),
        [recentUpgrades],
    );

    const bestDrop = useMemo(
        () => wins.length > 0 ? wins.reduce((a, b) => (a.target_price > b.target_price ? a : b)) : null,
        [wins],
    );

    const bestMultiplier = useMemo(() => {
        if (wins.length === 0) return null;
        let best = wins[0];
        let bestRatio = 0;
        for (const w of wins) {
            const ratio = w.bet_amount > 0 ? w.target_price / w.bet_amount : 0;
            if (ratio > bestRatio) {
                bestRatio = ratio;
                best = w;
            }
        }
        return { upgrade: best, ratio: Math.round(bestRatio) };
    }, [wins]);

    const lowestChanceWin = useMemo(
        () => wins.length > 0 ? wins.reduce((a, b) => (a.chance < b.chance ? a : b)) : null,
        [wins],
    );

    return (
        <div className="flex flex-col 550:flex-row gap-3.5 550:min-h-[391px] w-full">
            <StatCard
                label="Самый дорогой дроп"
                bigText={bestDrop ? formatKopecks(bestDrop.target_price) : '—'}
                upgrade={bestDrop}
                isPrice
            />
            <StatCard
                label="Лучший множитель"
                bigText={bestMultiplier ? `X${bestMultiplier.ratio}` : '—'}
                upgrade={bestMultiplier?.upgrade ?? null}
            />
            <StatCard
                label="Самый низкий шанс"
                bigText={lowestChanceWin ? `${Math.round(lowestChanceWin.chance)}%` : '—'}
                upgrade={lowestChanceWin}
            />
        </div>
    );
}

function splitPriceKopecks(kopecks: number): string[] {
    const rubles = kopecks / 100;
    const intPart = Math.floor(rubles);
    const decPart = Math.round((rubles - intPart) * 10) / 10;
    const groups = intPart
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
        .split(' ');
    if (decPart > 0) {
        return [...groups, ',' + decPart.toString().split('.')[1] + '₽'];
    }
    return [...groups, '₽'];
}

function StatCard({
    label, bigText, upgrade, isPrice = false,
}: {
    label: string;
    bigText: string;
    upgrade: UpgradeEntry | null;
    isPrice?: boolean;
}) {
    const rarity = upgrade ? mapRarityColor(upgrade.target_skin_rarity_color ?? null) : 'covrt';
    const { weapon, name } = upgrade ? parseSkinName(upgrade.target_skin_name) : { weapon: '—', name: '' };

    let bigTextParts: string[];
    if (isPrice && upgrade) {
        bigTextParts = splitPriceKopecks(upgrade.target_price);
    } else if (bigText.match(/^X\d+$/)) {
        bigTextParts = ['X', bigText.slice(1)];
    } else if (bigText.match(/^\d+%$/)) {
        bigTextParts = ['%', bigText.replace('%', '')];
    } else {
        bigTextParts = [bigText];
    }

    return (
        <div
            className={`rarity-${rarity} flex flex-col p-[18px] items-start relative w-full min-h-[160px] 550:min-h-0 overflow-hidden rounded-[14px] bg-[#BED4FF]/2 bg-linear-to-b from-transparent to-[var(--rarity-from)]`}
            style={{ borderBottom: '2px solid var(--rarity-accent)' }}
        >
            <ItemBackgroundLines />
            <span
                className="font-sf-display font-black text-center leading-[79%] absolute left-1/2 top-1/2 -translate-1/2 pointer-events-none select-none whitespace-nowrap"
                style={{
                    fontSize: '70px',
                    background: 'linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%)',
                    WebkitBackgroundClip: 'text',
                    WebkitTextFillColor: 'transparent',
                    backgroundClip: 'text',
                }}
            >
                {bigTextParts.join('')}
            </span>
            {upgrade?.target_skin_image && (
                <img src={upgrade.target_skin_image} className="absolute left-1/2 top-1/2 -translate-1/2 w-full max-w-[224px] h-full max-h-[168px] object-contain z-10" alt="" />
            )}
            <div className="flex flex-col absolute left-1/2 bottom-[40px] -translate-x-1/2 items-center z-10 max-w-[90%]">
                <span className="text-white font-sf-display font-medium text-[13px] leading-[104%] whitespace-nowrap">{weapon}</span>
                <span className="text-white font-gotham text-[13px] font-extralight leading-[104%] text-center whitespace-nowrap truncate max-w-full">{name}</span>
            </div>
            <span className="text-white font-sf-display text-[13px] leading-[120%] relative z-10 mt-auto">{label}</span>
        </div>
    );
}
