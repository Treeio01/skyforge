import AppLayout from '@/Layouts/AppLayout';
import UpgradeBlock from '@/Components/Upgrade/UpgradeBlock';
import VideoPreloader from '@/Components/Upgrade/VideoPreloader';
import {
    UPGRADE_VIDEO_PRELOAD_PRIORITY,
    upgradeVideoBackgroundSrcs,
} from '@/Components/Upgrade/upgradeVideos';
import { PageProps, Skin } from '@/types';
import { usePage } from '@inertiajs/react';
import { useCallback, useState } from 'react';

interface UpgradePageProps extends Record<string, unknown> {
    inventory: Array<{ id: number; skin: Skin; price_at_acquisition: number }>;
}

export default function Upgrade() {
    const { inventory } = usePage<PageProps<UpgradePageProps>>().props;
    const [backgroundPrimed, setBackgroundPrimed] = useState(false);

    const handlePriorityVideosLoaded = useCallback(() => {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.classList.add('hidden');
        }
        setBackgroundPrimed(true);
    }, []);

    return (
        <AppLayout>
            <VideoPreloader
                srcs={[...UPGRADE_VIDEO_PRELOAD_PRIORITY]}
                preload="auto"
                onAllLoaded={handlePriorityVideosLoaded}
            />
            {backgroundPrimed && (
                <VideoPreloader
                    srcs={upgradeVideoBackgroundSrcs()}
                    preload="metadata"
                />
            )}
            <UpgradeBlock inventory={inventory} />
        </AppLayout>
    );
}
