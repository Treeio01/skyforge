import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';
import { PageProps } from '@/types';

/**
 * Returns a wrapper function that checks auth before executing callback.
 * If guest — shows login modal instead.
 */
export function useAuthGuard() {
    const isGuest = !usePage<PageProps>().props.auth.user;

    const guard = useCallback(
        (callback: () => void) => {
            if (isGuest) {
                window.dispatchEvent(new Event('show-login-modal'));
                return;
            }
            callback();
        },
        [isGuest],
    );

    return { isGuest, guard };
}
