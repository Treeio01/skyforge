import ProfileStatCards from '@/Components/Profile/ProfileStatCards';
import { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';

interface DeferredStatProps {
    recentUpgrades?: Array<{
        id: number;
        target_skin_name: string;
        target_skin_image: string | null;
        target_skin_rarity_color: string | null;
        target_price: number;
        bet_amount: number;
        chance: number;
        result: 'win' | 'lose';
        created_at: string;
    }>;
}

export default function ProfileDeferredStatCards() {
    const { recentUpgrades = [] } = usePage<PageProps & DeferredStatProps>().props;

    return <ProfileStatCards recentUpgrades={recentUpgrades} />;
}
