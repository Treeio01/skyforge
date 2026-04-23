import { memo } from "react";
import SkinCard from "@/Components/Upgrade/SkinCard";
import { apiSkinToEntry } from "@/utils/skinHelpers";

type SkinEntry = ReturnType<typeof apiSkinToEntry>;

interface SkinGridProps {
    items: SkinEntry[];
    selected: Set<string | number>;
    onToggle: (id: string | number) => void;
    loading?: boolean;
}

export default memo(function SkinGrid({ items, selected, onToggle, loading }: SkinGridProps) {
    return (
        <div
            className="flex-1 overflow-y-auto skins-scroll p-2.5 bg-[#070A10] max-h-[calc(100vh-80px)]"
        >
            <div className="grid gap-1 grid-cols-[repeat(auto-fill,160px)] 1024:grid-cols-[repeat(auto-fill,200px)] justify-center">
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

            {loading && (
                <div className="flex justify-center py-4">
                    <span className="text-white/20 font-sf-display text-[13px] animate-pulse">Загрузка...</span>
                </div>
            )}

            {!loading && items.length === 0 && (
                <div className="flex justify-center py-12">
                    <span className="text-white/20 font-sf-display text-[13px]">Скины не найдены</span>
                </div>
            )}
        </div>
    );
});
