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
    toggleSelect: (id: string | number) => void;
    items: ReturnType<typeof apiSkinToEntry>[];
    selectedItems: ReturnType<typeof apiSkinToEntry>[];
    totalSelected: number;
    loading: boolean;
    scrollRef: React.RefObject<HTMLDivElement>;
    handleScroll: () => void;
    handleBuy: () => void;
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

    const { sort, direction } = parseSortOption(sortOption);

    const { skins, loading, loadMore } = useTargetSkins({
        search,
        sort,
        direction,
        minPrice,
        maxPrice,
        perPage: 150,
        inventoryPrice: null,
    });

    const items = useMemo(() => skins.map((s) => apiSkinToEntry(s)), [skins]);

    const selectedItems = useMemo(
        () => items.filter((s) => selected.has(s.id)),
        [items, selected],
    );

    const totalSelected = selectedItems.reduce((sum, s) => sum + s.priceKopecks, 0);

    const toggleSelect = useCallback((id: string | number) => {
        setSelected((prev) => {
            const next = new Set(prev);
            if (next.has(id)) next.delete(id);
            else next.add(id);
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

    const handleBuy = useCallback(() => {
        if (selected.size === 0) return;
        setBuying(true);
        const skinIds = selectedItems.map((s) => s.backendSkinId).filter(Boolean);
        router.post('/market/buy', { skin_ids: skinIds }, {
            preserveScroll: true,
            onSuccess: () => { setSelected(new Set()); setCartOpen(false); },
            onFinish: () => setBuying(false),
        });
    }, [selected, selectedItems]);

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
        items,
        selectedItems,
        totalSelected,
        loading,
        scrollRef,
        handleScroll,
        handleBuy,
    };
}
