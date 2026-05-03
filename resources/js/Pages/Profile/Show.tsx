import AppLayout from '@/Layouts/AppLayout';
import PageShell from '@/Components/Layout/PageShell';
import ProfileSidebar from '@/Components/Profile/ProfileSidebar';
import SellModal from '@/Components/Profile/SellModal';
import ProfileDeferredStatCards from '@/Pages/Profile/ProfileDeferredStatCards';
import ProfileDeferredTabs from '@/Pages/Profile/ProfileDeferredTabs';
import { ProfileStatCardsFallback, ProfileTabsFallback } from '@/Pages/Profile/ProfileDeferredFallbacks';
import { Deferred } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
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

export default function Show() {
    const { t } = useTranslation();
    const { profile } = usePage<PageProps<ProfilePageProps>>().props;

    const [sellModalVisible, setSellModalVisible] = useState(false);
    const [sellMode, setSellMode] = useState<'all' | 'selected'>('all');
    const [selectedSkins, setSelectedSkins] = useState<Set<number>>(new Set());

    const toggleSkinSelection = (id: number) => {
        setSelectedSkins((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }

            return next;
        });
    };

    const openSellModal = (mode: 'all' | 'selected') => {
        setSellMode(mode);
        setSellModalVisible(true);
    };

    return (
        <AppLayout>
            <PageShell title={t('header.profile')} subtitle={profile.username}>
                <div className="flex flex-col gap-4 1024:gap-6 w-full">
                    <div className="flex flex-col 1024:flex-row gap-4 1024:gap-6 items-stretch w-full p-4 1024:p-6 rounded-[14px] bg-[#11161F]">
                        <ProfileSidebar profile={profile} />
                        <div className="flex-1 min-w-0 flex flex-col gap-6">
                            <Deferred
                                data={['inventory', 'recentUpgrades']}
                                fallback={<ProfileStatCardsFallback />}
                            >
                                <ProfileDeferredStatCards />
                            </Deferred>
                        </div>
                    </div>

                    <Deferred
                        data={['inventory', 'recentUpgrades']}
                        fallback={<ProfileTabsFallback />}
                    >
                        <ProfileDeferredTabs
                            selectedSkins={selectedSkins}
                            onToggleSkin={toggleSkinSelection}
                            onSellAll={() => openSellModal('all')}
                            onSellSelected={() => openSellModal('selected')}
                        />
                    </Deferred>
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
