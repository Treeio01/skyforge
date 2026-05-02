import AppLayout from '@/Layouts/AppLayout';
import UpgradeBlock from '@/Components/Upgrade/UpgradeBlock';
import VideoPreloader from '@/Components/Upgrade/VideoPreloader';
import { ALL_UPGRADE_VIDEO_SRCS } from '@/Components/Upgrade/upgradeVideos';
import { PageProps, Skin } from '@/types';
import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';

interface UpgradePageProps extends Record<string, unknown> {
    inventory: Array<{ id: number; skin: Skin; price_at_acquisition: number }>;
}

export default function Upgrade() {
    const { inventory } = usePage<PageProps<UpgradePageProps>>().props;

    const handleAllVideosLoaded = useCallback(() => {
        const loader = document.getElementById('page-loader');
        if (loader) loader.classList.add('hidden');
    }, []);

    return (
        <AppLayout>
            <VideoPreloader
                srcs={ALL_UPGRADE_VIDEO_SRCS}
                onAllLoaded={handleAllVideosLoaded}
            />
            <UpgradeBlock inventory={inventory} />
        </AppLayout>
    );
}
