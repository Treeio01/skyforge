import { router } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { InventoryIcon } from "@/Components/UI/Icons";
import SkinCard, { SkinEntry } from "./SkinCard";
import SkinsPanel from "./SkinsPanel";
import EmptySkinCard from "./EmptySkinCard";
import UpgradeTargetToolbar, { PriceSort } from "./UpgradeTargetToolbar";

interface UpgradeInventoryPanelProps {
    inventoryItems: SkinEntry[];
    selectedInventory: string | number | null;
    panelLocked: boolean;
    onSelectInventory: (id: string | number) => void;
    priceSort: PriceSort;
    minPrice: string;
    search: string;
    onPriceSortChange: (v: PriceSort) => void;
    onMinPriceChange: (v: string) => void;
    onSearchChange: (v: string) => void;
}

export default function UpgradeInventoryPanel({
    inventoryItems,
    selectedInventory,
    panelLocked,
    onSelectInventory,
    priceSort,
    minPrice,
    search,
    onPriceSortChange,
    onMinPriceChange,
    onSearchChange,
}: UpgradeInventoryPanelProps) {
    const { t } = useTranslation();
    const hasFilters = !!(search || minPrice || priceSort);

    return (
        <SkinsPanel
            icon={<InventoryIcon />}
            title={t('upgrade.your_skins')}
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
            {inventoryItems.length === 0 ? (
                hasFilters ? (
                    <div className="col-span-full flex flex-col items-center justify-center min-h-[260px] gap-1.5 text-center px-6">
                        <span className="text-white/55 font-gotham font-medium text-[14px] leading-[120%]">
                            {t('upgrade.no_filter_match')}
                        </span>
                        <span className="text-white/30 font-sf-display text-[11px] leading-[140%] max-w-[240px]">
                            {t('upgrade.no_filter_match_hint')}
                        </span>
                    </div>
                ) : (
                    <div className="col-span-full flex flex-col items-center justify-center min-h-[280px] gap-4 text-center px-6">
                        <div className="w-[145px]">
                            <EmptySkinCard onClick={() => router.visit("/market")} />
                        </div>
                        <div className="flex flex-col gap-1">
                            <span className="text-white/55 font-gotham font-medium text-[15px] leading-[120%]">
                                {t('upgrade.empty_inventory')}
                            </span>
                            <span className="text-white/30 font-sf-display text-[12px] leading-[140%] max-w-[260px]">
                                {t('upgrade.empty_inventory_hint')}
                            </span>
                        </div>
                    </div>
                )
            ) : (
                <>
                    {inventoryItems.map((skin) => (
                        <SkinCard
                            key={skin.id}
                            {...skin}
                            selected={selectedInventory === skin.id}
                            dimmed={
                                selectedInventory !== null &&
                                selectedInventory !== skin.id
                            }
                            onClick={
                                panelLocked
                                    ? undefined
                                    : () => onSelectInventory(skin.id)
                            }
                        />
                    ))}
                    <EmptySkinCard onClick={() => router.visit("/market")} />
                </>
            )}
        </SkinsPanel>
    );
}
