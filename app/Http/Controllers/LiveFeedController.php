<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Upgrade;
use Illuminate\Http\JsonResponse;

class LiveFeedController extends Controller
{
    public function index(): JsonResponse
    {
        $feed = Upgrade::with(['user', 'targetSkin'])
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
                'is_fake' => $u->is_fake,
                'created_at' => $u->created_at?->toISOString(),
            ])
            ->toArray();

        return response()->json(['data' => $feed]);
    }
}
