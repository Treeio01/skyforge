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

    // Login modal
    modalVisible: boolean;
    setModalVisible: (v: boolean) => void;
    adultChecked: boolean;
    setAdultChecked: (v: boolean) => void;
    termsChecked: boolean;
    setTermsChecked: (v: boolean) => void;
    canLogin: boolean;
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

    // ─── Login modal ────────────────────────────────────────
    const [modalVisible, setModalVisible] = useState(false);
    const [adultChecked, setAdultChecked] = useState(false);
    const [termsChecked, setTermsChecked] = useState(false);
    const canLogin = adultChecked && termsChecked;

    useEffect(() => {
        if (!isGuest) return;
        const show = () => setModalVisible(true);
        window.addEventListener('show-login-modal', show);
        return () => window.removeEventListener('show-login-modal', show);
    }, [isGuest]);

    const timersRef = useRef<number[]>([]);
    const clearTimers = () => {
        timersRef.current.forEach((t) => window.clearTimeout(t));
        timersRef.current = [];
    };

    // ─── Derived ─────────────────────────────────────────────
    const inventoryItems = useMemo(
        () => inventory.map(inventoryItemToEntry),
        [inventory],
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
        );
    }, [inventorySkin, targetSkin]);

    const canStart = useMemo(
        () =>
            stage === "idle" &&
            !!inventorySkin &&
            !!targetSkin &&
            targetSkin.priceKopecks > inventorySkin.priceKopecks &&
            isChanceValid(chance),
        [stage, inventorySkin, targetSkin, chance],
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
            setMultiplier(m);
            setActiveQuick(m);
            if (!inventorySkin) return;

            const invKop = inventorySkin.priceKopecks;
            const idealKopecks = invKop * m;
            const minKopecks = invKop + 1;

            try {
                const res = await axios.get('/api/skins', {
                    params: {
                        per_page: 5,
                        sort: 'price',
                        direction: 'asc',
                        min_price: Math.round(idealKopecks * 0.8),
                        max_price: Math.round(idealKopecks * 1.5),
                    },
                });
                let skins = res.data?.data;

                // Если в диапазоне пусто — расширяем
                if (!skins?.length) {
                    const fallback = await axios.get('/api/skins', {
                        params: {
                            per_page: 1,
                            sort: 'price',
                            direction: 'asc',
                            min_price: minKopecks,
                        },
                    });
                    skins = fallback.data?.data;
                }

                if (skins?.length) {
                    // Берём ближайший к ideal
                    const sorted = [...skins].sort(
                        (a, b) => Math.abs(a.price - idealKopecks) - Math.abs(b.price - idealKopecks),
                    );
                    const entry = apiSkinToEntry(sorted[0], 'target-');
                    setAutoPickedTarget(entry);
                    setSelectedTarget(entry.id);
                }
            } catch {
                const pick = pickTargetForMultiplier(inventorySkin, m, targetItems);
                if (pick) {
                    setAutoPickedTarget(pick);
                    setSelectedTarget(pick.id);
                }
            }
        },
        [inventorySkin, targetItems],
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

        // Login modal
        modalVisible,
        setModalVisible,
        adultChecked,
        setAdultChecked,
        termsChecked,
        setTermsChecked,
        canLogin,
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
