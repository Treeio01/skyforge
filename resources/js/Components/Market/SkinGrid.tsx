import React, { memo } from "react";
import SkinCard from "@/Components/Upgrade/SkinCard";
import { apiSkinToEntry } from "@/utils/skinHelpers";
import { SkeletonSkinCard } from "@/Components/UI/Skeleton";

type SkinEntry = ReturnType<typeof apiSkinToEntry>;

interface SkinGridProps {
    items: SkinEntry[];
    selected: Set<string | number>;
    onToggle: (id: string | number) => void;
    loading?: boolean;
    containerRef?: React.RefObject<HTMLDivElement>;
    onScroll?: () => void;
}

export default memo(function SkinGrid({ items, selected, onToggle, loading, containerRef, onScroll }: SkinGridProps) {
    if (loading) {
        return (
            <div className="grid grid-cols-2 xs:grid-cols-3 lg:grid-cols-4 wide:grid-cols-5 gap-3">
                {Array.from({ length: 12 }).map((_, i) => <SkeletonSkinCard key={i} />)}
            </div>
        );
    }

    return (
        <div
            ref={containerRef}
            onScroll={onScroll}
            className="flex-1 overflow-y-auto skins-scroll p-2.5 bg-[#070A10] max-h-[calc(100vh-80px)]"
        >
            <div className="grid grid-cols-2 xs:grid-cols-3 lg:grid-cols-4 wide:grid-cols-5 gap-3">
                {items.map((skin) => (
                    <SkinCard
                        key={skin.id}
                        {...skin}
                        selected={selected.has(skin.id)}
                        dimmed={selected.size > 0 && !selected.has(skin.id)}
                        onClick={() => onToggle(skin.id)}
                    />
                ))}
            </div>

            {items.length === 0 && (
                <div className="flex justify-center py-12">
                    <span className="text-white/20 font-sf-display text-[13px]">Скины не найдены</span>
                </div>
            )}
        </div>
    );
});
