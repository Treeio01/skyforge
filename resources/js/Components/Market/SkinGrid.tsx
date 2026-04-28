import React, { memo, useRef, useEffect, useState } from "react";
import { useVirtualizer } from "@tanstack/react-virtual";
import SkinCard from "@/Components/Upgrade/SkinCard";
import { apiSkinToEntry } from "@/utils/skinHelpers";
import { SkeletonSkinCard } from "@/Components/UI/Skeleton";

type SkinEntry = ReturnType<typeof apiSkinToEntry>;

const CARD_HEIGHT = 126;
const CARD_MIN_WIDTH = 150;
const GAP = 12;

function useColumns(containerRef: React.RefObject<HTMLDivElement | null>): number {
    const [cols, setCols] = useState(5);

    useEffect(() => {
        const el = containerRef.current;
        if (!el) return;

        const observer = new ResizeObserver(([entry]) => {
            const available = entry.contentRect.width;
            const count = Math.max(2, Math.floor((available + GAP) / (CARD_MIN_WIDTH + GAP)));
            setCols(count);
        });

        observer.observe(el);
        return () => observer.disconnect();
    }, [containerRef]);

    return cols;
}

interface SkinGridProps {
    items: SkinEntry[];
    selected: Set<string | number>;
    onToggle: (skin: SkinEntry) => void;
    loading?: boolean;
    hasMore?: boolean;
    containerRef?: React.RefObject<HTMLDivElement>;
    onScroll?: () => void;
}

export default memo(function SkinGrid({ items, selected, onToggle, loading, hasMore, containerRef, onScroll }: SkinGridProps) {
    const internalRef = useRef<HTMLDivElement>(null);
    const parentRef = containerRef ?? internalRef;
    const columns = useColumns(parentRef);

    // Когда есть ещё страницы — показываем только полные ряды, чтобы не было «обрубленного» хвоста.
    const visibleItems = hasMore ? items.slice(0, Math.floor(items.length / columns) * columns) : items;
    const rows = Math.ceil(visibleItems.length / columns);

    const virtualizer = useVirtualizer({
        count: rows,
        getScrollElement: () => parentRef.current,
        estimateSize: () => CARD_HEIGHT + GAP,
        overscan: 3,
    });

    const isInitialLoad = loading && items.length === 0;

    return (
        <div
            ref={parentRef as React.RefObject<HTMLDivElement>}
            className="flex-1 min-h-0 overflow-y-auto skins-scroll -mx-3 1024:-mx-4 px-3 1024:px-4"
            onScroll={onScroll}
        >
            {isInitialLoad ? (
                <div className="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-3">
                    {Array.from({ length: 24 }).map((_, i) => (
                        <SkeletonSkinCard key={i} index={i} />
                    ))}
                </div>
            ) : items.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-16 gap-2">
                    <span className="text-white/40 font-sf-display text-[14px]">Скины не найдены</span>
                    <span className="text-white/20 font-sf-display text-[12px]">Попробуйте изменить фильтры или поиск</span>
                </div>
            ) : (
                <>
                    <div style={{ height: virtualizer.getTotalSize(), position: "relative" }}>
                        {virtualizer.getVirtualItems().map((virtualRow) => {
                            const rowStart = virtualRow.index * columns;
                            const rowItems = visibleItems.slice(rowStart, rowStart + columns);

                            return (
                                <div
                                    key={virtualRow.key}
                                    style={{
                                        position: "absolute",
                                        top: virtualRow.start,
                                        left: 0,
                                        right: 0,
                                        display: "grid",
                                        gridTemplateColumns: `repeat(${columns}, 1fr)`,
                                        gap: GAP,
                                    }}
                                >
                                    {rowItems.map((skin) => (
                                        <SkinCard
                                            key={skin.id}
                                            {...skin}
                                            selected={selected.has(skin.id)}
                                            onClick={() => onToggle(skin)}
                                        />
                                    ))}
                                </div>
                            );
                        })}
                    </div>
                    {loading && items.length > 0 && (
                        <div className="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-3 mt-3">
                            {Array.from({ length: columns * 2 }).map((_, i) => (
                                <SkeletonSkinCard key={`more-${i}`} index={i} />
                            ))}
                        </div>
                    )}
                </>
            )}
        </div>
    );
});
