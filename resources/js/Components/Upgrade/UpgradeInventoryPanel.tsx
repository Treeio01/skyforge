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
    // Заполняем панель плейсхолдерами, чтобы при малом инвентаре сетка
    // выглядела ровно, а не «комом» в углу. 15 — с запасом на 4×4 грид.
    const placeholderCount = Math.max(0, 15 - inventoryItems.length);

    return (
        <SkinsPanel icon={<InventoryIcon />} title="Ваши скины">
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
            {Array.from({ length: placeholderCount }).map((_, i) => (
                <EmptySkinCard key={`placeholder-${i}`} />
            ))}
        </SkinsPanel>
    );
}
