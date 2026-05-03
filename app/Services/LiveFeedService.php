<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Upgrade;
use Illuminate\Support\Facades\Cache;

class LiveFeedService
{
    private const FEED_LIMIT = 20;

    private const CACHE_KEY = 'live_feed.recent_payload';

    private const CACHE_TTL_SECONDS = 10;

    /**
     * Drop cached JSON so the next HTTP hit sees fresh rows (WebSocket still
     * pushes live updates to connected clients).
     */
    public function forgetRecentCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<int, array<string, mixed>> */
    public function recent(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): array {
            return $this->loadRecentFromDatabase();
        });
    }

    /** @return array<int, array<string, mixed>> */
    private function loadRecentFromDatabase(): array
    {
        return Upgrade::query()
            ->with(['user', 'targetSkin'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::FEED_LIMIT)
            ->get()
            ->map(fn (Upgrade $u) => [
                'id' => $u->id,
                'username' => $u->user->username,
                'avatar_url' => $u->user->avatar_url,
                'target_skin_name' => $u->targetSkin->market_hash_name,
                'target_skin_image' => $u->targetSkin->image_path
                    ? asset('storage/'.$u->targetSkin->image_path)
                    : null,
                'rarity_color' => $u->targetSkin->rarity_color,
                'chance' => $u->chance,
                'result' => $u->result->value,
                'is_fake' => $u->is_fake,
                'created_at' => $u->created_at?->toISOString(),
            ])
            ->all();
    }
}
