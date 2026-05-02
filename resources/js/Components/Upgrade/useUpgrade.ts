import { apiSkinToEntry, inventoryItemToEntry } from "@/utils/skinHelpers";
import { useAuthGuard } from "@/hooks/useAuthGuard";
import { useTargetSkins } from "@/hooks/useTargetSkins";
import { router, usePage } from "@inertiajs/react";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import axios from "axios";
import { DefuseOutcome } from "./DefuseOverlay";
import { SkinEntry } from "./SkinCard";
import { PriceSort } from "./UpgradeTargetToolbar";
import { UpgradeState } from "./upgradeVideos";
import {
    calculateChance,
    isChanceValid,
    deriveMultiplier,
    pickTargetForMultiplier,
    QuickMultiplier,
    DEFAULT_UPGRADE_SETTINGS,
} from "./upgradeCalculations";
import { PageProps, Skin } from "@/types";

type SkinId = string | number;
export type Stage = "idle" | "closing" | "playing" | "playing_two" | "result";
export type { QuickMultiplier };

export const MULTIPLIERS: QuickMultiplier[] = [2, 3, 5, 10];

const RESULT_DISPLAY_MS = 5000;

function toggleSingle(prev: SkinId | null, id: SkinId): SkinId | null {
    return prev === id ? null : id;
}

function stageToVideoState(
    stage: Stage,
    outcome: DefuseOutcome | null,
): UpgradeState {
    if (stage === "playing") return "playing";
    if (stage === "playing_two") return "playing_two";
    if (stage === "result") return outcome === "success" ? "won" : "lose";
    return "idle"; // idle + closing
}

interface UseUpgradeProps {
    inventory: Array<{ id: number; skin: Skin; price_at_acquisition: number }>;
}

export interface UseUpgradeReturn {
    // Selection
    selectedInventory: SkinId | null;
    selectedTarget: SkinId | null;
    handleSelectInventory: (id: SkinId) => void;
    handleSelectTarget: (id: SkinId) => void;

    // Filter state
    priceSort: PriceSort;
    setPriceSort: (v: PriceSort) => void;
    minPrice: string;
    setMinPrice: (v: string) => void;
    search: string;
    setSearch: (v: string) => void;

    // Game state
    multiplier: number | null;
    activeQuick: QuickMultiplier | null;
    stage: Stage;
    outcome: DefuseOutcome | null;
    resultSkin: SkinEntry | null;

    isGuest: boolean;

    // Derived
    inventoryItems: SkinEntry[];
    inventorySkin: SkinEntry | null;
    targetItems: SkinEntry[];
    targetSkin: SkinEntry | null;
    chance: number;
    canStart: boolean;
    videoState: UpgradeState;
    panelLocked: boolean;
    targetsLoading: boolean;
    hasMore: boolean;
    loadMore: () => void;

    // Handlers
    handleMultiplierChange: (m: QuickMultiplier) => Promise<void>;
    handleReset: () => void;
    handleGo: () => void;
    handleClosingComplete: () => void;
    handleVideoEnded: () => void;
    handleRemoveInventory: () => void;
    handleRemoveTarget: () => void;
}

export function useUpgrade({ inventory }: UseUpgradeProps): UseUpgradeReturn {
    const { guard, isGuest } = useAuthGuard();
    const { upgradeSettings = DEFAULT_UPGRADE_SETTINGS } = usePage<PageProps>().props;

    // ─── Selection ───────────────────────────────────────────
    const [selectedInventory, setSelectedInventory] = useState<SkinId | null>(
        null,
    );
    const [selectedTarget, setSelectedTarget] = useState<SkinId | null>(null);

    // ─── Filter state ────────────────────────────────────────
    const [priceSort, setPriceSort] = useState<PriceSort>(null);
    const [minPrice, setMinPrice] = useState("");
    const [search, setSearch] = useState("");

    // ─── Game state ──────────────────────────────────────────
    const [multiplier, setMultiplier] = useState<number | null>(null);
    const [activeQuick, setActiveQuick] = useState<QuickMultiplier | null>(null);
    const [stage, setStage] = useState<Stage>("idle");
    const [outcome, setOutcome] = useState<DefuseOutcome | null>(null);
    const [resultSkin, setResultSkin] = useState<SkinEntry | null>(null);

    const timersRef = useRef<number[]>([]);
    const clearTimers = () => {
        timersRef.current.forEach((t) => window.clearTimeout(t));
        timersRef.current = [];
    };

    // ─── Inventory snapshot (frozen during animation) ──────────
    // Inertia replaces `inventory` immediately after the upgrade request,
    // which would let the user see the won/lost item before the animation
    // finishes. Hold onto the previous snapshot until stage returns to "idle".
    const [stableInventory, setStableInventory] = useState(inventory);
    useEffect(() => {
        if (stage === "idle") setStableInventory(inventory);
    }, [inventory, stage]);

    // ─── Derived ─────────────────────────────────────────────
    const inventoryItems = useMemo(
        () => stableInventory.map(inventoryItemToEntry),
        [stableInventory],
    );

    const inventorySkin = useMemo(
        () => inventoryItems.find((s) => s.id === selectedInventory) ?? null,
        [inventoryItems, selectedInventory],
    );

    // ─── Target skins from API ───────────────────────────────
    const invPrice = inventorySkin ? inventorySkin.priceKopecks : null;

    const { skins: rawTargetSkins, loading: targetsLoading, hasMore, loadMore } = useTargetSkins({
        search,
        sort: priceSort ? 'price' : null,
        direction: priceSort === 'asc' ? 'asc' : 'desc',
        minPrice,
        inventoryPrice: invPrice,
    });

    // Скин подобранный через множитель (может не быть в загруженном списке)
    const [autoPickedTarget, setAutoPickedTarget] = useState<SkinEntry | null>(null);

    const targetItems = useMemo(
        () => {
            const base = rawTargetSkins.map((s) => apiSkinToEntry(s, 'target-'));
            if (autoPickedTarget && !base.some((s) => s.id === autoPickedTarget.id)) {
                return [autoPickedTarget, ...base];
            }
            return base;
        },
        [rawTargetSkins, autoPickedTarget],
    );

    const targetSkin = useMemo(
        () => {
            if (selectedTarget && autoPickedTarget?.id === selectedTarget) return autoPickedTarget;
            return targetItems.find((s) => s.id === selectedTarget) ?? null;
        },
        [targetItems, selectedTarget, autoPickedTarget],
    );

    const chance = useMemo(() => {
        if (!inventorySkin || !targetSkin) return 0;
        return calculateChance(
            inventorySkin.priceKopecks,
            targetSkin.priceKopecks,
            upgradeSettings,
        );
    }, [inventorySkin, targetSkin, upgradeSettings]);

    const canStart = useMemo(
        () =>
            stage === "idle" &&
            !!inventorySkin &&
            !!targetSkin &&
            targetSkin.priceKopecks > inventorySkin.priceKopecks &&
            isChanceValid(chance, upgradeSettings),
        [stage, inventorySkin, targetSkin, chance, upgradeSettings],
    );

    const videoState = stageToVideoState(stage, outcome);
    const panelLocked = stage !== "idle";

    // ─── Auto-behaviors ──────────────────────────────────────
    // Автопересчёт множителя при смене скинов
    useEffect(() => {
        if (!inventorySkin || !targetSkin) return;
        const derived = deriveMultiplier(inventorySkin, targetSkin);
        if (derived !== null) setMultiplier(derived);
    }, [inventorySkin, targetSkin]);

    const handleMultiplierChange = useCallback(
        async (m: QuickMultiplier) => {
            setActiveQuick(m);
            if (!inventorySkin) return;

            const invKop = inventorySkin.priceKopecks;
            const idealKopecks = invKop * m;
            const minKopecks = invKop + 1;

            try {
                // Probe both sides of the ideal price so dense pricing on one
                // side can't starve the other side and skew the pick away from x{m}.
                const [aboveRes, belowRes] = await Promise.all([
                    axios.get('/api/skins', {
                        params: {
                            per_page: 10,
                            sort: 'price',
                            direction: 'asc',
                            min_price: Math.round(idealKopecks),
                            max_price: Math.round(idealKopecks * 1.5),
                        },
                    }),
                    axios.get('/api/skins', {
                        params: {
                            per_page: 10,
                            sort: 'price',
                            direction: 'desc',
                            min_price: Math.max(minKopecks, Math.round(idealKopecks * 0.5)),
                            max_price: Math.max(minKopecks, Math.round(idealKopecks) - 1),
                        },
                    }),
                ]);

                let skins = [
                    ...(aboveRes.data?.data ?? []),
                    ...(belowRes.data?.data ?? []),
                ];

                if (!skins.length) {
                    const fallback = await axios.get('/api/skins', {
                        params: {
                            per_page: 1,
                            sort: 'price',
                            direction: 'asc',
                            min_price: minKopecks,
                        },
                    });
                    skins = fallback.data?.data ?? [];
                }

                if (skins.length) {
                    const sorted = [...skins].sort(
                        (a, b) => Math.abs(a.price - idealKopecks) - Math.abs(b.price - idealKopecks),
                    );
                    const entry = apiSkinToEntry(sorted[0], 'target-');
                    setAutoPickedTarget(entry);
                    setSelectedTarget(entry.id);
                }
            } catch {
                const pick = pickTargetForMultiplier(inventorySkin, m, targetItems, upgradeSettings);
                if (pick) {
                    setAutoPickedTarget(pick);
                    setSelectedTarget(pick.id);
                }
            }
        },
        [inventorySkin, targetItems, upgradeSettings],
    );

    const handleSelectInventory = useCallback((id: SkinId) => {
        setSelectedInventory((prev) => toggleSingle(prev, id));
        // Сброс target — он мог стать невалидным
        setSelectedTarget(null);
        setMultiplier(null); setActiveQuick(null);
    }, []);

    const handleSelectTarget = useCallback((id: SkinId) => {
        setSelectedTarget((prev) => toggleSingle(prev, id));
        setAutoPickedTarget(null);
        setActiveQuick(null);
    }, []);

    // ─── Flow ────────────────────────────────────────────────
    const handleReset = useCallback(() => {
        clearTimers();
        setStage("idle");
        setOutcome(null);
        setResultSkin(null);
        setSelectedInventory(null);
        setSelectedTarget(null);
        setMultiplier(null); setActiveQuick(null);
    }, []);

    const handleGo = useCallback(() => {
        return guard(() => {
        if (!canStart || !inventorySkin || !targetSkin) return;

        setResultSkin(targetSkin);

        router.post(
            "/upgrade",
            {
                user_skin_ids: inventorySkin.backendUserSkinId
                    ? [inventorySkin.backendUserSkinId]
                    : [],
                balance_amount: 0,
                target_skin_id: targetSkin.backendSkinId,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    const flash = (page.props as PageProps).flash;
                    const isWin = !!flash?.success;
                    setOutcome(isWin ? "success" : "fail");
                    setStage("closing");
                },
                onError: (errors) => {
                    console.error('[UPGRADE] onError:', errors);
                    setStage("idle");
                },
            },
        );
        });
    }, [canStart, inventorySkin, targetSkin, guard]);

    const handleRemoveInventory = useCallback(() => {
        setSelectedInventory(null);
        setSelectedTarget(null);
        setMultiplier(null); setActiveQuick(null);
    }, []);

    const handleRemoveTarget = useCallback(() => {
        setSelectedTarget(null);
        setMultiplier(null); setActiveQuick(null);
    }, []);

    const handleClosingComplete = useCallback(() => {
        setStage((s) => (s === "closing" ? "playing" : s));
    }, []);

    const handleVideoEnded = useCallback(() => {
        setStage((s) => {
            if (s === "playing") return "playing_two";
            if (s === "playing_two") return "result";
            return s;
        });
    }, []);

    // playing_two → result (по таймеру DefuseOverlay).
    useEffect(() => {
        if (stage !== "playing_two" || !outcome) return;
        const defuseMs =
            outcome === "success" ? 5000 : 3000;
        const t = window.setTimeout(() => {
            setStage("result");
        }, defuseMs);
        timersRef.current.push(t);
        return () => window.clearTimeout(t);
    }, [stage, outcome]);

    // result → idle (автосброс после показа результата).
    useEffect(() => {
        if (stage !== "result") return;
        const t = window.setTimeout(handleReset, RESULT_DISPLAY_MS);
        timersRef.current.push(t);
        return () => window.clearTimeout(t);
    }, [stage, handleReset]);

    useEffect(() => () => clearTimers(), []);

    return {
        // Selection
        selectedInventory,
        selectedTarget,
        handleSelectInventory,
        handleSelectTarget,

        // Filter state
        priceSort,
        setPriceSort,
        minPrice,
        setMinPrice,
        search,
        setSearch,

        // Game state
        multiplier,
        activeQuick,
        stage,
        outcome,
        resultSkin,

        isGuest,

        // Derived
        inventoryItems,
        inventorySkin,
        targetItems,
        targetSkin,
        chance,
        canStart,
        videoState,
        panelLocked,
        targetsLoading,
        hasMore,
        loadMore,

        // Handlers
        handleMultiplierChange,
        handleReset,
        handleGo,
        handleClosingComplete,
        handleVideoEnded,
        handleRemoveInventory,
        handleRemoveTarget,
    };
}
