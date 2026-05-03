import ProfileTabs from '@/Components/Profile/ProfileTabs';
import { PageProps, Skin } from '@/types';
import { usePage } from '@inertiajs/react';

interface DeferredTabsProps {
    inventory?: Array<{ id: number; skin: Skin; price_at_acquisition: number }>;
    recentUpgrades?: Array<{
        id: number;
        target_skin_name: string;
        target_skin_image: string | null;
        target_skin_rarity_color: string | null;
        bet_skin_name: string | null;
        bet_skin_image: string | null;
        bet_skin_rarity_color: string | null;
        target_price: number;
        bet_amount: number;
        chance: number;
        result: 'win' | 'lose';
        created_at: string;
    }>;
}

interface ProfileDeferredTabsProps {
    selectedSkins: Set<number>;
    onToggleSkin: (id: number) => void;
    onSellAll: () => void;
    onSellSelected: () => void;
}

export default function ProfileDeferredTabs({
    selectedSkins,
    onToggleSkin,
    onSellAll,
    onSellSelected,
}: ProfileDeferredTabsProps) {
    const { inventory = [], recentUpgrades = [] } = usePage<PageProps & DeferredTabsProps>().props;

    return (
        <ProfileTabs
            inventory={inventory}
            recentUpgrades={recentUpgrades}
            selectedSkins={selectedSkins}
            onToggleSkin={onToggleSkin}
            onSellAll={onSellAll}
            onSellSelected={onSellSelected}
        />
    );
}
