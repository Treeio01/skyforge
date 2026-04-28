import { InventoryIcon } from "@/Components/UI/Icons";
import SkinCard, { SkinEntry } from "./SkinCard";
import SkinsPanel from "./SkinsPanel";
import EmptySkinCard from "./EmptySkinCard";

interface UpgradeInventoryPanelProps {
    inventoryItems: SkinEntry[];
    selectedInventory: string | number | null;
    panelLocked: boolean;
    onSelectInventory: (id: string | number) => void;
}

export default function UpgradeInventoryPanel({
    inventoryItems,
    selectedInventory,
    panelLocked,
    onSelectInventory,
}: UpgradeInventoryPanelProps) {
    return (
        <SkinsPanel icon={<InventoryIcon />} title="Ваши скины">
            {inventoryItems.length === 0 ? (
                <div className="col-span-full flex flex-col items-center justify-center min-h-[280px] gap-4 text-center px-6">
                    <div className="w-[145px]">
                        <EmptySkinCard />
                    </div>
                    <div className="flex flex-col gap-1">
                        <span className="text-white/55 font-gotham font-medium text-[15px] leading-[120%]">
                            В инвентаре нет скинов
                        </span>
                        <span className="text-white/30 font-sf-display text-[12px] leading-[140%] max-w-[260px]">
                            Пополните баланс и получите скин в Маркете или через апгрейд
                        </span>
                    </div>
                </div>
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
                    <EmptySkinCard />
                </>
            )}
        </SkinsPanel>
    );
}
