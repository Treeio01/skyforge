export type UpgradeState = 'idle' | 'playing' | 'playing_two' | 'won' | 'lose';
export type DeviceKind = 'pc' | 'md' | 'mb';

// TODO: когда появятся отдельные md-файлы — поменять пути у md на свои.
// Пока md использует мобильные видео.
export const UPGRADE_VIDEOS: Record<UpgradeState, Record<DeviceKind, string>> = {
    idle: {
        pc: '/assets/video/cap_pc.mp4',
        md: '/assets/video/cap_mb.mp4',
        mb: '/assets/video/cap_mb.mp4',
    },
    playing: {
        pc: '/assets/video/play_pc.mp4',
        md: '/assets/video/play_mb.mp4',
        mb: '/assets/video/play_mb.mp4',
    },
    playing_two: {
        pc: '/assets/video/play_two_pc.mp4',
        md: '/assets/video/play_two_mb.mp4',
        mb: '/assets/video/play_two_mb.mp4',
    },
    won: {
        pc: '/assets/video/won_pc.mp4',
        md: '/assets/video/won_mb.mp4',
        mb: '/assets/video/won_mb.mp4',
    },
    lose: {
        pc: '/assets/video/lose_pc.mp4',
        md: '/assets/video/lose_mb.mp4',
        mb: '/assets/video/lose_mb.mp4',
    },
};

export const ALL_UPGRADE_VIDEO_SRCS: string[] = Array.from(
    new Set(
        Object.values(UPGRADE_VIDEOS).flatMap((byDevice) =>
            Object.values(byDevice),
        ),
    ),
);

/**
 * Distinct idle clips only — enough to show the upgrade screen; other states
 * prefetch in the background after this batch finishes.
 */
export const UPGRADE_VIDEO_PRELOAD_PRIORITY: readonly string[] = Array.from(
    new Set([UPGRADE_VIDEOS.idle.pc, UPGRADE_VIDEOS.idle.md]),
);

export function upgradeVideoBackgroundSrcs(): string[] {
    const priority = new Set(UPGRADE_VIDEO_PRELOAD_PRIORITY);

    return ALL_UPGRADE_VIDEO_SRCS.filter((src) => ! priority.has(src));
}
