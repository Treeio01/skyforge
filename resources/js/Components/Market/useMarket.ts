import { useMemo, useRef, useState, useCallback } from "react";
import { router } from "@inertiajs/react";
import { useTargetSkins } from "@/hooks/useTargetSkins";
import { apiSkinToEntry } from "@/utils/skinHelpers";

export type SortOption = "price_asc" | "price_desc" | "name_asc" | "name_desc";

export const SORT_OPTIONS: { label: string; value: SortOption }[] = [
    { label: "Цена ↑", value: "price_asc" },
    { label: "Цена ↓", value: "price_desc" },
    { label: "Имя А-Я", value: "name_asc" },
    { label: "Имя Я-А", value: "name_desc" },
];

function parseSortOption(s: SortOption) {
    const [sort, dir] = s.split("_") as ["price" | "name", "asc" | "desc"];
    return { sort, direction: dir };
}

export interface UseMarketReturn {
    search: string;
    setSearch: (v: string) => void;
    sortOption: SortOption;
    setSortOption: (v: SortOption) => void;
    minPrice: string;
    setMinPrice: (v: string) => void;
    maxPrice: string;
    setMaxPrice: (v: string) => void;
    filtersOpen: boolean;
    setFiltersOpen: (v: boolean) => void;
    cartOpen: boolean;
    setCartOpen: (v: boolean) => void;
    buying: boolean;
    setBuying: (v: boolean) => void;
    selected: Set<string | number>;
    setSelected: (v: Set<string | number>) => void;
    toggleSelect: (skin: ReturnType<typeof apiSkinToEntry>) => void;
    clearSelected: () => void;
    items: ReturnType<typeof apiSkinToEntry>[];
    selectedItems: ReturnType<typeof apiSkinToEntry>[];
    totalSelected: number;
    loading: boolean;
    hasMore: boolean;
    scrollRef: React.RefObject<HTMLDivElement>;
    handleScroll: () => void;
    handleBuy: () => void;
    applyFilters: () => void;
}

export function useMarket(): UseMarketReturn {
    const [search, setSearch] = useState("");
    const [sortOption, setSortOption] = useState<SortOption>("price_asc");
    const [minPrice, setMinPrice] = useState("");
    const [maxPrice, setMaxPrice] = useState("");
    const [filtersOpen, setFiltersOpen] = useState(false);
    const [cartOpen, setCartOpen] = useState(false);
    const [buying, setBuying] = useState(false);
    const [selected, setSelected] = useState<Set<string | number>>(new Set());
    const [selectedData, setSelectedData] = useState<Map<string | number, ReturnType<typeof apiSkinToEntry>>>(new Map());

    const { sort, direction } = parseSortOption(sortOption);

    const { skins, loading, hasMore, loadMore } = useTargetSkins({
        search,
        sort,
        direction,
        minPrice,
        maxPrice,
        perPage: 72,
        inventoryPrice: null,
    });

    // В items вкладываем выделенные скины ПЕРЕД отфильтрованной выдачей,
    // чтобы пользователь всегда видел свой выбор, даже если фильтр их прячет.
    const items = useMemo(() => {
        const base = skins.map((s) => apiSkinToEntry(s));
        const baseIds = new Set(base.map((i) => i.id));
        const pinned = Array.from(selectedData.values()).filter((s) => !baseIds.has(s.id));
        return [...pinned, ...base];
    }, [skins, selectedData]);

    const selectedItems = useMemo(
        () => Array.from(selectedData.values()).filter((s) => selected.has(s.id)),
        [selectedData, selected],
    );

    const totalSelected = useMemo(
        () => selectedItems.reduce((sum, s) => sum + s.priceKopecks, 0),
        [selectedItems],
    );

    const toggleSelect = useCallback((skin: ReturnType<typeof apiSkinToEntry>) => {
        setSelected((prev) => {
            const next = new Set(prev);
            if (next.has(skin.id)) next.delete(skin.id);
            else next.add(skin.id);
            return next;
        });
        setSelectedData((prev) => {
            const next = new Map(prev);
            if (next.has(skin.id)) next.delete(skin.id);
            else next.set(skin.id, skin);
            return next;
        });
    }, []);

    const scrollRef = useRef<HTMLDivElement>(null);

    const handleScroll = useCallback(() => {
        const el = scrollRef.current;
        if (!el) return;
        if (el.scrollHeight - el.scrollTop - el.clientHeight < 200) {
            loadMore();
        }
    }, [loadMore]);

    const clearSelected = useCallback(() => {
        setSelected(new Set());
        setSelectedData(new Map());
    }, []);

    const applyFilters = useCallback(() => {
        router.reload({
            data: {
                search: search,
                min_price: minPrice,
                max_price: maxPrice,
                sort: sort,
                direction: direction,
            },
            only: ['skins'],
            preserveUrl: false,
        });
    }, [search, minPrice, maxPrice, sort, direction]);

    const handleBuy = useCallback(() => {
        if (selectedItems.length === 0) return;
        setBuying(true);
        const skinIds = selectedItems.map((s) => s.backendSkinId).filter(Boolean);
        router.post('/market/buy', { skin_ids: skinIds }, {
            preserveScroll: true,
            onSuccess: () => { setSelected(new Set()); setSelectedData(new Map()); setCartOpen(false); },
            onFinish: () => setBuying(false),
        });
    }, [selectedItems]);

    return {
        search,
        setSearch,
        sortOption,
        setSortOption,
        minPrice,
        setMinPrice,
        maxPrice,
        setMaxPrice,
        filtersOpen,
        setFiltersOpen,
        cartOpen,
        setCartOpen,
        buying,
        setBuying,
        selected,
        setSelected,
        toggleSelect,
        clearSelected,
        items,
        selectedItems,
        totalSelected,
        loading,
        hasMore,
        scrollRef,
        handleScroll,
        handleBuy,
        applyFilters,
    };
}
