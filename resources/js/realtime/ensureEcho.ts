import type Echo from 'laravel-echo';

let echoBootPromise: Promise<void> | null = null;

/**
 * Dynamically chunks Echo + Pusher and opens the WebSocket on first demand.
 */
export async function ensureEcho(): Promise<InstanceType<typeof Echo> | undefined> {
    if (typeof window === 'undefined') {
        return undefined;
    }

    if (window.Echo) {
        return window.Echo;
    }

    if (!echoBootPromise) {
        echoBootPromise = import('./echo-bootstrap').then((m) => {
            m.bootstrapEcho();
        });
    }

    await echoBootPromise;

    return window.Echo;
}
