import AppLayout from '@/Layouts/AppLayout';
import PageShell from '@/Components/Layout/PageShell';
import ProfileSidebar from '@/Components/Profile/ProfileSidebar';
import ProfileStatCards from '@/Components/Profile/ProfileStatCards';
import ProfileTabs from '@/Components/Profile/ProfileTabs';
import SellModal from '@/Components/Profile/SellModal';
import { usePage } from '@inertiajs/react';
import { PageProps, Skin } from '@/types';
import { useState } from 'react';

interface ProfilePageProps extends Record<string, unknown> {
    profile: {
        id: number;
        username: string;
        avatar_url: string | null;
        steam_id: string;
        balance: number;
        trade_url: string | null;
        total_deposited: number;
        total_withdrawn: number;
        total_upgraded: number;
        total_won: number;
        created_at: string;
    };
    inventory: Array<{ id: number; skin: Skin; price_at_acquisition: number }>;
    recentUpgrades: Array<{
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

export default function Show() {
    const { profile, inventory, recentUpgrades } = usePage<PageProps<ProfilePageProps>>().props;

    const [sellModalVisible, setSellModalVisible] = useState(false);
    const [sellMode, setSellMode] = useState<'all' | 'selected'>('all');
    const [selectedSkins, setSelectedSkins] = useState<Set<number>>(new Set());

    const toggleSkinSelection = (id: number) => {
        setSelectedSkins((prev) => {
            const next = new Set(prev);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    };

    return (
        <AppLayout>
            <PageShell title="Профиль" subtitle={profile.username}>
                <div className="flex flex-col gap-4 1024:gap-6 w-full">
                    <div className="flex flex-col 1024:flex-row gap-4 1024:gap-6 items-stretch w-full p-4 1024:p-6 rounded-[14px] bg-[#0E131C]">
                        <ProfileSidebar profile={profile} />
                        <ProfileStatCards recentUpgrades={recentUpgrades} />
                    </div>

                    <ProfileTabs
                        inventory={inventory}
                        recentUpgrades={recentUpgrades}
                        selectedSkins={selectedSkins}
                        onToggleSkin={toggleSkinSelection}
                        onSellAll={() => { setSellMode('all'); setSellModalVisible(true); }}
                        onSellSelected={() => { setSellMode('selected'); setSellModalVisible(true); }}
                    />
                </div>
            </PageShell>

            <SellModal
                visible={sellModalVisible}
                onClose={() => setSellModalVisible(false)}
                mode={sellMode}
                selectedIds={selectedSkins}
                onSuccess={() => setSelectedSkins(new Set())}
            />
        </AppLayout>
    );
}
