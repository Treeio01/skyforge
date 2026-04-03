<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class LiveFeedController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Redis::lrange('feed:recent', 0, 49);

        $feed = array_map(fn (string $item) => json_decode($item, true), $items ?: []);

        return response()->json(['data' => $feed]);
    }
}
