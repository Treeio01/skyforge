<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\SkinBriefResource;
use App\Models\Skin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SkinController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Skin::query()->active()->availableForUpgrade();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('sort')) {
            $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

            match ($request->input('sort')) {
                'price' => $query->orderBy('price', $direction),
                'name' => $query->orderBy('market_hash_name', $direction),
                default => $query->orderByDesc('price'),
            };
        } else {
            $query->orderByDesc('price');
        }

        return SkinBriefResource::collection(
            $query->paginate($request->input('per_page', 50)),
        );
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('q', '');

        if (mb_strlen($search) < 2) {
            return SkinBriefResource::collection(collect());
        }

        $skins = Skin::query()
            ->active()
            ->availableForUpgrade()
            ->whereRaw('MATCH(market_hash_name) AGAINST(? IN BOOLEAN MODE)', [$search.'*'])
            ->orderByDesc('price')
            ->limit(50)
            ->get();

        return SkinBriefResource::collection($skins);
    }
}
