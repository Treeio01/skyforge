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
        </SkinsPanel>
    );
}
