<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\LiveFeedService;
use Illuminate\Http\JsonResponse;

class LiveFeedController extends Controller
{
    public function index(LiveFeedService $service): JsonResponse
    {
        return response()->json(['data' => $service->recent()]);
    }
}
