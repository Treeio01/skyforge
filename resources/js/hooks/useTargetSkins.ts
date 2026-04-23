import axios from 'axios';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useToast } from '@/Components/UI/Toast';
import { Skin } from '@/types';

interface UseTargetSkinsParams {
    search: string;
    sort: 'price' | 'name' | null;
    direction: 'asc' | 'desc';
    minPrice: string;
    maxPrice?: string;
    perPage?: number;
    inventoryPrice: number | null;
}

interface PaginatedResponse {
    data: Skin[];
    links: {
        next: string | null;
    };
    meta: {
        current_page: number;
    };
}

const DEBOUNCE_MS = 400;

export function useTargetSkins(params: UseTargetSkinsParams) {
    const { search, sort, direction, minPrice, maxPrice, perPage = 50, inventoryPrice } = params;

    const [skins, setSkins] = useState<Skin[]>([]);
    const [loading, setLoading] = useState(false);
    const [hasMore, setHasMore] = useState(false);

    const { toast } = useToast();

    const nextPageUrlRef = useRef<string | null>(null);
    const abortControllerRef = useRef<AbortController | null>(null);
    const debounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const loadingRef = useRef(false);

    const buildInitialUrl = useCallback(() => {
        const urlParams = new URLSearchParams({ per_page: String(perPage) });

        if (sort) {
            urlParams.set('sort', sort);
            urlParams.set('direction', direction);
        }

        const minPriceParsed = parseFloat(minPrice);
        const minPriceKopecks = !isNaN(minPriceParsed) && minPriceParsed > 0
            ? Math.round(minPriceParsed * 100)
            : 0;
        const invPriceKopecks = inventoryPrice !== null && inventoryPrice > 0
            ? inventoryPrice + 1
            : 0;

        const effectiveMin = Math.max(minPriceKopecks, invPriceKopecks);
        if (effectiveMin > 0) {
            urlParams.set('min_price', String(effectiveMin));
        }

        const maxPriceParsed = parseFloat(maxPrice ?? '');
        if (!isNaN(maxPriceParsed) && maxPriceParsed > 0) {
            urlParams.set('max_price', String(Math.round(maxPriceParsed * 100)));
        }

        if (search.trim().length >= 2) {
            return `/api/skins/search?q=${encodeURIComponent(search.trim())}&${urlParams.toString()}`;
        }

        return `/api/skins?${urlParams.toString()}`;
    }, [search, sort, direction, minPrice, maxPrice, inventoryPrice]);

    const fetchPage = useCallback(
        (url: string, append: boolean) => {
            abortControllerRef.current?.abort();
            abortControllerRef.current = new AbortController();

            setLoading(true);
            loadingRef.current = true;

            axios
                .get<PaginatedResponse>(url, { signal: abortControllerRef.current.signal })
                .then((res) => {
                    const fetched = res.data.data;
                    setSkins((prev) => (append ? [...prev, ...fetched] : fetched));
                    nextPageUrlRef.current = res.data.links?.next ?? null;
                    setHasMore(res.data.links?.next !== null);
                })
                .catch((err) => {
                    if (axios.isCancel(err)) {
                        return;
                    }
                    toast('error', 'Не удалось загрузить скины для апгрейда');
                })
                .finally(() => {
                    setLoading(false);
                    loadingRef.current = false;
                });
        },
        [],
    );

    // Debounce all filter changes
    useEffect(() => {
        nextPageUrlRef.current = null;

        if (debounceTimerRef.current) {
            clearTimeout(debounceTimerRef.current);
            abortControllerRef.current?.abort();
        }

        if (search.trim().length > 0 && search.trim().length < 2) {
            return;
        }

        // Сбрасываем список сразу — чтобы показать скелетон, не дожидаясь дебаунса
        setSkins([]);
        setLoading(true);

        debounceTimerRef.current = setTimeout(() => {
            fetchPage(buildInitialUrl(), false);
        }, DEBOUNCE_MS);

        return () => {
            if (debounceTimerRef.current) {
                clearTimeout(debounceTimerRef.current);
            }
        };
    }, [search, sort, direction, minPrice, maxPrice, inventoryPrice, buildInitialUrl, fetchPage]);

    const loadMore = useCallback(() => {
        if (loadingRef.current || !nextPageUrlRef.current) {
            return;
        }

        fetchPage(nextPageUrlRef.current, true);
    }, [fetchPage]);

    useEffect(() => {
        return () => {
            abortControllerRef.current?.abort();
            if (debounceTimerRef.current) {
                clearTimeout(debounceTimerRef.current);
            }
        };
    }, []);

    return { skins, loading, hasMore, loadMore };
}
