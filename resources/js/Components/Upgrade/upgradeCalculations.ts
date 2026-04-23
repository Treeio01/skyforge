import { SkinEntry } from "./SkinCard";

export type QuickMultiplier = 2 | 3 | 5 | 10;

// Формула шанса совпадает с бэком: (bet / target) * (1 - houseEdge/100) * 100
export const HOUSE_EDGE_PERCENT = 5;
export const MIN_CHANCE = 1;
export const MAX_CHANCE = 95;

export function calculateChance(invKopecks: number, tgtKopecks: number): number {
    if (tgtKopecks <= 0) return 0;
    const raw = (invKopecks / tgtKopecks) * (1 - HOUSE_EDGE_PERCENT / 100) * 100;
    return Math.min(MAX_CHANCE, Math.max(0, raw));
}

export function isChanceValid(chance: number): boolean {
    return chance >= MIN_CHANCE && chance <= MAX_CHANCE;
}

export function canBeTarget(inv: SkinEntry, tgt: SkinEntry): boolean {
    if (tgt.id === inv.id) return false;
    if (tgt.priceKopecks <= inv.priceKopecks) return false;
    return isChanceValid(calculateChance(inv.priceKopecks, tgt.priceKopecks));
}

export function deriveMultiplier(inv: SkinEntry, tgt: SkinEntry): number | null {
    if (inv.priceKopecks <= 0 || tgt.priceKopecks <= inv.priceKopecks) return null;
    return tgt.priceKopecks / inv.priceKopecks;
}

export function pickTargetForMultiplier(
    inv: SkinEntry,
    m: QuickMultiplier,
    pool: SkinEntry[],
): SkinEntry | null {
    const ideal = inv.priceKopecks * m;
    const candidates = pool.filter((s) => canBeTarget(inv, s));
    if (candidates.length === 0) return null;
    candidates.sort(
        (a, b) => Math.abs(a.priceKopecks - ideal) - Math.abs(b.priceKopecks - ideal),
    );
    return candidates[0];
}
