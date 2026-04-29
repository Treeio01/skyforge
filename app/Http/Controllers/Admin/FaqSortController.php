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
        $data = $request->validate([
            'data' => ['required', 'array'],
            'data.*' => ['integer'],
        ]);

        foreach ($data['data'] as $index => $id) {
            FaqItem::whereKey($id)->update(['sort_order' => $index]);
        }

        return response()->json(['status' => true]);
    }
}
