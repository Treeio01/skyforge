import AppLayout from '@/Layouts/AppLayout';
import UpgradeBlock from '@/Components/Upgrade/UpgradeBlock';
import VideoPreloader from '@/Components/Upgrade/VideoPreloader';
import { ALL_UPGRADE_VIDEO_SRCS } from '@/Components/Upgrade/upgradeVideos';
import { PageProps, Skin } from '@/types';
import { usePage } from '@inertiajs/react';

interface UpgradePageProps extends Record<string, unknown> {
    inventory: Array<{ id: number; skin: Skin; price_at_acquisition: number }>;
    balance: number;
}

export default function Upgrade() {
    const { inventory, balance } = usePage<PageProps<UpgradePageProps>>().props;

    return (
        <AppLayout>
            <VideoPreloader srcs={ALL_UPGRADE_VIDEO_SRCS} />
            <UpgradeBlock inventory={inventory} balance={balance} />
        </AppLayout>
    );
}
