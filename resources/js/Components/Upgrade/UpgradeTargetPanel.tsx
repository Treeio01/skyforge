import { UpgradeTargetIcon } from "@/Components/UI/Icons";
import { SkeletonSkinCard } from "@/Components/UI/Skeleton";
import SkinCard, { SkinEntry } from "./SkinCard";
import SkinsPanel from "./SkinsPanel";
import UpgradeTargetToolbar, { PriceSort } from "./UpgradeTargetToolbar";

interface UpgradeTargetPanelProps {
    targetItems: SkinEntry[];
    selectedTarget: string | number | null;
    panelLocked: boolean;
    targetsLoading: boolean;
    priceSort: PriceSort;
    minPrice: string;
    search: string;
    onSelectTarget: (id: string | number) => void;
    onScrollEnd: () => void;
    onPriceSortChange: (v: PriceSort) => void;
    onMinPriceChange: (v: string) => void;
    onSearchChange: (v: string) => void;
}

export default function UpgradeTargetPanel({
    targetItems,
    selectedTarget,
    panelLocked,
    targetsLoading,
    priceSort,
    minPrice,
    search,
    onSelectTarget,
    onScrollEnd,
    onPriceSortChange,
    onMinPriceChange,
    onSearchChange,
}: UpgradeTargetPanelProps) {
    return (
        <SkinsPanel
            icon={<UpgradeTargetIcon />}
            title="Скин апгрейда"
            onScrollEnd={onScrollEnd}
            toolbar={
                <UpgradeTargetToolbar
                    priceSort={priceSort}
                    onPriceSortChange={onPriceSortChange}
                    minPrice={minPrice}
                    onMinPriceChange={onMinPriceChange}
                    search={search}
                    onSearchChange={onSearchChange}
                />
            }
        >
            {targetsLoading ? (
                Array.from({ length: 16 }).map((_, i) => (
                    <SkeletonSkinCard key={`skel-${priceSort}-${minPrice}-${search}-${i}`} index={i} />
                ))
            ) : targetItems.length === 0 ? (
                <div className="col-span-full flex flex-col items-center justify-center min-h-[280px] gap-1.5 text-center px-6">
                    <span className="text-white/55 font-gotham font-medium text-[15px] leading-[120%]">
                        Подходящих скинов не найдено
                    </span>
                    <span className="text-white/30 font-sf-display text-[12px] leading-[140%] max-w-[260px]">
                        Попробуйте сбросить фильтры или выбрать другой скин из инвентаря
                    </span>
                </div>
            ) : (
                targetItems.map((skin) => (
                    <SkinCard
                        key={skin.id}
                        {...skin}
                        selected={selectedTarget === skin.id}
                        dimmed={
                            selectedTarget !== null &&
                            selectedTarget !== skin.id
                        }
                        onClick={
                            panelLocked
                                ? undefined
                                : () => onSelectTarget(skin.id)
                        }
                    />
                ))
            )}
        </SkinsPanel>
    );
}
