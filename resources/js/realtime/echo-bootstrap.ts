import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Initialise Laravel Echo/Reverb. Called lazily via ensureEcho() so heavy WS
 * code loads only when a component actually needs realtime.
 */
export function bootstrapEcho(): void {
    if (typeof window === 'undefined' || window.Echo) {
        return;
    }

    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
