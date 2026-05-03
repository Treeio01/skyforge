import type { FeedItem } from '@/types';
import axios from 'axios';

/** Shared across SPA navigations; aligns with CDN `Cache-Control` on `/api/live-feed`. */
let cached: FeedItem[] | null = null;
let cachedAtMs = 0;
const TTL_MS = 9500;

export async function fetchLiveFeedItems(force = false): Promise<FeedItem[]> {
    const now = Date.now();
    if (! force && cached && now - cachedAtMs < TTL_MS) {
        return cached;
    }

    const response = await axios.get<{ data: FeedItem[] }>('/api/live-feed');
    cached = response.data?.data ?? [];
    cachedAtMs = now;

    return cached ?? [];
}

export function invalidateLiveFeedCache(): void {
    cached = null;
    cachedAtMs = 0;
}
