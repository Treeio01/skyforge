<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Enums\UserSkinSource;
use App\Enums\UserSkinStatus;
use App\Http\Resources\SkinBriefResource;
use App\Models\Skin;
use App\Models\UserSkin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SkinController extends Controller
{
    public function market(): Response
    {
        return Inertia::render('Market/Index');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Skin::query()->active()->availableForUpgrade();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->input('max_price'));
        }

        if ($request->filled('sort')) {
            $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

            match ($request->input('sort')) {
                'price' => $query->orderBy('price', $direction),
                'name' => $query->orderBy('market_hash_name', $direction),
                default => $query->orderBy('price'),
            };
        } else {
            $query->orderBy('price');
        }

        return SkinBriefResource::collection(
            $query->paginate($request->input('per_page', 50))->withQueryString(),
        );
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $search = $request->input('q', '');

        if (mb_strlen($search) < 2) {
            return SkinBriefResource::collection(collect());
        }

        $query = Skin::query()
            ->active()
            ->availableForUpgrade()
            ->where('market_hash_name', 'like', '%'.str_replace('%', '', $search).'%');

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->input('max_price'));
        }

        if ($request->filled('sort')) {
            $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

            match ($request->input('sort')) {
                'price' => $query->orderBy('price', $direction),
                'name' => $query->orderBy('market_hash_name', $direction),
                default => $query->orderBy('price'),
            };
        } else {
            $query->orderBy('price');
        }

        return SkinBriefResource::collection(
            $query->paginate($request->input('per_page', 50))->withQueryString(),
        );
    }

    public function buy(Request $request): RedirectResponse
    {
        $request->validate([
            'skin_ids' => 'required|array|min:1',
            'skin_ids.*' => 'integer|exists:skins,id',
        ]);

        $user = $request->user();
        $skinIds = $request->input('skin_ids');

        return DB::transaction(function () use ($user, $skinIds) {
            $user->lockForUpdate()->first();
            $user->refresh();

            $skins = Skin::whereIn('id', $skinIds)->where('is_active', true)->get();

            if ($skins->count() !== count($skinIds)) {
                return back()->withErrors(['skin_ids' => 'Некоторые скины недоступны']);
            }

            $totalPrice = $skins->sum('price');

            if ($user->balance < $totalPrice) {
                return back()->with('error', 'Недостаточно средств. Нужно: '.number_format($totalPrice / 100, 2, ',', ' ').' ₽');
            }

            $balanceBefore = $user->balance;
            $user->decrement('balance', $totalPrice);

            foreach ($skins as $skin) {
                UserSkin::create([
                    'user_id' => $user->id,
                    'skin_id' => $skin->id,
                    'price_at_acquisition' => $skin->price,
                    'source' => UserSkinSource::Deposit,
                    'status' => UserSkinStatus::Available,
                ]);
            }

            $user->transactions()->create([
                'type' => TransactionType::Withdrawal,
                'amount' => -$totalPrice,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $totalPrice,
                'description' => 'Покупка '.$skins->count().' скинов на рынке',
            ]);

            return back()->with('success', 'Куплено '.$skins->count().' скинов на сумму '.number_format($totalPrice / 100, 2, ',', ' ').' ₽');
        });
    }
}
