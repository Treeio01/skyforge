import React, { memo, useRef, useEffect, useState } from "react";
import { useVirtualizer } from "@tanstack/react-virtual";
import SkinCard from "@/Components/Upgrade/SkinCard";
import { apiSkinToEntry } from "@/utils/skinHelpers";
import { SkeletonSkinCard } from "@/Components/UI/Skeleton";

type SkinEntry = ReturnType<typeof apiSkinToEntry>;

const CARD_HEIGHT = 260;
const GAP = 12;

function useColumns(): number {
    const [cols, setCols] = useState(() => {
        if (typeof window === "undefined") return 4;
        const w = window.innerWidth;
        if (w < 550) return 2;
        if (w < 1024) return 3;
        if (w < 1155) return 4;
        return 5;
    });

    useEffect(() => {
        function update() {
            const w = window.innerWidth;
            if (w < 550) setCols(2);
            else if (w < 1024) setCols(3);
            else if (w < 1155) setCols(4);
            else setCols(5);
        }
        window.addEventListener("resize", update);
        return () => window.removeEventListener("resize", update);
    }, []);

    return cols;
}

interface SkinGridProps {
    items: SkinEntry[];
    selected: Set<string | number>;
    onToggle: (id: string | number) => void;
    loading?: boolean;
    containerRef?: React.RefObject<HTMLDivElement>;
    onScroll?: () => void;
}

export default memo(function SkinGrid({ items, selected, onToggle, loading, containerRef, onScroll }: SkinGridProps) {
    const parentRef = useRef<HTMLDivElement>(null);
    const columns = useColumns();
    const rows = Math.ceil(items.length / columns);

    const virtualizer = useVirtualizer({
        count: rows,
        getScrollElement: () => parentRef.current,
        estimateSize: () => CARD_HEIGHT + GAP,
        overscan: 3,
    });

    if (loading) {
        return (
            <div className="grid grid-cols-2 xs:grid-cols-3 lg:grid-cols-4 wide:grid-cols-5 gap-3">
                {Array.from({ length: 12 }).map((_, i) => (
                    <SkeletonSkinCard key={i} />
                ))}
            </div>
        );
    }

    return (
        <div
            ref={parentRef}
            className="flex-1 overflow-y-auto skins-scroll p-2.5 bg-[#070A10] max-h-[calc(100vh-80px)]"
            onScroll={onScroll}
        >
            {items.length === 0 ? (
                <div className="flex justify-center py-12">
                    <span className="text-white/20 font-sf-display text-[13px]">Скины не найдены</span>
                </div>
            ) : (
                <div style={{ height: virtualizer.getTotalSize(), position: "relative" }}>
                    {virtualizer.getVirtualItems().map((virtualRow) => {
                        const rowStart = virtualRow.index * columns;
                        const rowItems = items.slice(rowStart, rowStart + columns);

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
                                        dimmed={selected.size > 0 && !selected.has(skin.id)}
                                        onClick={() => onToggle(skin.id)}
                                    />
                                ))}
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
});
