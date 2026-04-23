import { UpgradeTargetIcon } from "@/Components/UI/Icons";
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
            {targetItems.map((skin) => (
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
            ))}
            {targetsLoading && (
                <div className="col-span-full text-center text-white/30 text-sm py-2">
                    Загрузка...
                </div>
            )}
        </SkinsPanel>
    );
}
