import { SkinEntry } from "./SkinCard";
import { UpgradeSettings } from "@/types";

export type QuickMultiplier = 2 | 3 | 5 | 10;

export const DEFAULT_UPGRADE_SETTINGS: UpgradeSettings = {
    houseEdge: 5,
    minChance: 1,
    maxChance: 95,
    minBetAmount: 100,
    maxBetAmount: 5_000_000,
    cooldownSeconds: 2,
};

// Формула шанса совпадает с бэком: (bet / target) * (1 - houseEdge/100) * 100
export function calculateChance(
    invKopecks: number,
    tgtKopecks: number,
    settings: UpgradeSettings = DEFAULT_UPGRADE_SETTINGS,
): number {
    if (tgtKopecks <= 0) return 0;
    const raw = (invKopecks / tgtKopecks) * (1 - settings.houseEdge / 100) * 100;
    return Math.min(settings.maxChance, Math.max(settings.minChance, raw));
}

export function isChanceValid(
    chance: number,
    settings: UpgradeSettings = DEFAULT_UPGRADE_SETTINGS,
): boolean {
    return chance >= settings.minChance && chance <= settings.maxChance;
}

export function canBeTarget(
    inv: SkinEntry,
    tgt: SkinEntry,
    settings: UpgradeSettings = DEFAULT_UPGRADE_SETTINGS,
): boolean {
    if (tgt.id === inv.id) return false;
    if (tgt.priceKopecks <= inv.priceKopecks) return false;
    return isChanceValid(calculateChance(inv.priceKopecks, tgt.priceKopecks, settings), settings);
}

export function deriveMultiplier(inv: SkinEntry, tgt: SkinEntry): number | null {
    if (inv.priceKopecks <= 0 || tgt.priceKopecks <= inv.priceKopecks) return null;
    return tgt.priceKopecks / inv.priceKopecks;
}

export function pickTargetForMultiplier(
    inv: SkinEntry,
    m: QuickMultiplier,
    pool: SkinEntry[],
    settings: UpgradeSettings = DEFAULT_UPGRADE_SETTINGS,
): SkinEntry | null {
    const ideal = inv.priceKopecks * m;
    const candidates = pool.filter((s) => canBeTarget(inv, s, settings));
    if (candidates.length === 0) return null;
    candidates.sort(
        (a, b) => Math.abs(a.priceKopecks - ideal) - Math.abs(b.priceKopecks - ideal),
    );
    return candidates[0];
}
