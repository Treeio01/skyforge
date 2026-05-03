import type { Page } from '@inertiajs/core';

import type { Flash } from '@/types';

/** Inertia keeps session flash both in nested `props.flash` and root `page.flash`; merge both. */
export function mergedFlashPayload(page: Page): Flash {
    const nested = ((page.props as { flash?: Partial<Flash> }).flash ?? {}) as Partial<Flash>;
    const top = (page.flash ?? {}) as Record<string, unknown>;

    const error = ((top.error as string | null | undefined) ?? nested.error ?? null) as string | null;
    const success = ((top.success as string | null | undefined) ?? nested.success ?? null) as string | null;
    const upgrade_roll = (
        ((top.upgrade_roll as Flash['upgrade_roll']) ?? nested.upgrade_roll ?? null) as Flash['upgrade_roll']
    );

    return { error, success, upgrade_roll };
}
