<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqSortController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'data' => ['required', 'string'],
        ]);

        $ids = array_filter(
            array_map('intval', explode(',', $request->string('data')->toString())),
            fn (int $id) => $id > 0,
        );

        foreach (array_values($ids) as $index => $id) {
            FaqItem::whereKey($id)->update(['sort_order' => $index]);
        }

        return response()->json(['status' => true]);
    }
}
