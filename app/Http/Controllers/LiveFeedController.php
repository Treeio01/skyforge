<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Upgrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class LiveFeedController extends Controller
{
    public function index(): JsonResponse
    {
        // Фейковые записи из Redis
        $fakeItems = Redis::lrange('feed:recent', 0, 29);
        $fakeFeed = array_map(fn (string $item) => json_decode($item, true), $fakeItems ?: []);

        // Реальные апгрейды из БД
        $realFeed = Upgrade::with(['user', 'targetSkin'])
            ->latest('created_at')
            ->limit(20)
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
                'created_at' => $u->created_at?->toISOString(),
            ])
            ->toArray();

        // Мержим: реальные + фейковые, сортируем по дате, лимит 20
        $merged = array_merge($realFeed, $fakeFeed);
        usort($merged, fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return response()->json(['data' => array_slice($merged, 0, 20)]);
    }
}
