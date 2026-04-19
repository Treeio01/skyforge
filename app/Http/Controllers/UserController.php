<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Enums\UserSkinStatus;
use App\Http\Requests\UpdateTradeUrlRequest;
use App\Http\Resources\SkinBriefResource;
use App\Http\Resources\UserProfileResource;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        $inventory = $user->userSkins()
            ->where('status', UserSkinStatus::Available)
            ->with('skin')
            ->get()
            ->map(fn ($us) => [
                'id' => $us->id,
                'skin' => (new SkinBriefResource($us->skin))->resolve($request),
                'price_at_acquisition' => $us->price_at_acquisition,
            ]);

        $recentUpgrades = $user->upgrades()
            ->with(['targetSkin', 'items.skin'])
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function ($u) {
                $betItem = $u->items->first();
                $betSkin = $betItem?->skin;

                return [
                    'id' => $u->id,
                    'target_skin_name' => $u->targetSkin->market_hash_name,
                    'target_skin_image' => $u->targetSkin->image_path ? asset('storage/'.$u->targetSkin->image_path) : null,
                    'target_skin_rarity_color' => $u->targetSkin->rarity_color,
                    'bet_skin_name' => $betSkin?->market_hash_name,
                    'bet_skin_image' => $betSkin?->image_path ? asset('storage/'.$betSkin->image_path) : null,
                    'bet_skin_rarity_color' => $betSkin?->rarity_color,
                    'target_price' => $u->target_price,
                    'bet_amount' => $u->bet_amount,
                    'chance' => $u->chance,
                    'result' => $u->result->value,
                    'created_at' => $u->created_at?->toISOString(),
                ];
            });

        return Inertia::render('Profile/Show', [
            'profile' => (new UserProfileResource($user))->resolve($request),
            'inventory' => $inventory,
            'recentUpgrades' => $recentUpgrades,
        ]);
    }

    public function updateTradeUrl(UpdateTradeUrlRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('success', 'Trade URL обновлён.');
    }

    public function sellSkins(Request $request): RedirectResponse
    {
        $request->validate([
            'mode' => 'required|in:all,selected',
            'ids' => 'array',
            'ids.*' => 'integer',
        ]);

        $user = $request->user();
        $mode = $request->input('mode');

        return DB::transaction(function () use ($user, $mode, $request) {
            $user->lockForUpdate()->first();
            $user->refresh();

            $query = $user->userSkins()->where('status', UserSkinStatus::Available)->with('skin');

            if ($mode === 'selected') {
                $ids = $request->input('ids', []);

                if (empty($ids)) {
                    return back()->withErrors(['ids' => 'Не выбраны скины']);
                }
                $query->whereIn('id', $ids);
            }

            $skins = $query->get();

            if ($skins->isEmpty()) {
                return back()->withErrors(['ids' => 'Нет скинов для продажи']);
            }

            $totalAmount = 0;

            foreach ($skins as $userSkin) {
                $totalAmount += $userSkin->skin->price;
                $userSkin->update(['status' => UserSkinStatus::Burned]);
            }

            $balanceBefore = $user->balance;
            $user->increment('balance', $totalAmount);

            $user->transactions()->create([
                'type' => TransactionType::Bonus,
                'amount' => $totalAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $totalAmount,
                'description' => "Продажа {$skins->count()} скинов",
            ]);

            return back()->with('success', "Продано {$skins->count()} скинов на сумму ".number_format($totalAmount / 100, 2, ',', ' ').' ₽');
        });
    }

    public function deposits(Request $request): JsonResponse
    {
        $deposits = $request->user()->transactions()
            ->where('type', TransactionType::Deposit)
            ->orderByDesc('created_at')
            ->take(50)
            ->get(['id', 'amount', 'balance_after', 'description', 'created_at']);

        return response()->json($deposits);
    }

    public function redeemPromo(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $user = $request->user();
        $code = strtoupper(trim($request->input('code')));

        $promo = PromoCode::where('code', $code)->active()->notExpired()->first();

        if (! $promo) {
            return back()->withErrors(['code' => 'Промокод не найден или истёк']);
        }

        if ($promo->max_uses && $promo->times_used >= $promo->max_uses) {
            return back()->withErrors(['code' => 'Промокод исчерпан']);
        }

        $alreadyUsed = $user->promoCodeUsages()->where('promo_code_id', $promo->id)->exists();

        if ($alreadyUsed) {
            return back()->withErrors(['code' => 'Вы уже использовали этот промокод']);
        }

        if ($promo->type === 'deposit_bonus') {
            $user->promoCodeUsages()->create([
                'promo_code_id' => $promo->id,
                'amount' => $promo->amount,
                'created_at' => now(),
            ]);

            $promo->increment('times_used');

            return back()->with('success', 'Промокод применён! +'.$promo->amount.'% к следующему пополнению.');
        }

        return DB::transaction(function () use ($user, $promo) {
            $user->lockForUpdate()->first();
            $user->refresh();

            $balanceBefore = $user->balance;
            $user->increment('balance', $promo->amount);

            $user->transactions()->create([
                'type' => TransactionType::Bonus,
                'amount' => $promo->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $promo->amount,
                'description' => "Промокод: {$promo->code}",
            ]);

            $user->promoCodeUsages()->create([
                'promo_code_id' => $promo->id,
                'amount' => $promo->amount,
                'created_at' => now(),
            ]);

            $promo->increment('times_used');

            return back()->with('success', 'Промокод применён! +'.number_format($promo->amount / 100, 2, ',', ' ').' ₽');
        });
    }

    public function history(Request $request): Response
    {
        $user = $request->user();
        $type = $request->input('type', 'all');

        $validTypes = array_column(TransactionType::cases(), 'value');

        if ($type !== 'all' && ! in_array($type, $validTypes, true)) {
            $type = 'all';
        }

        $transactions = $user->transactions()
            ->when($type !== 'all', fn ($q) => $q->where('type', $type))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Profile/History', [
            'transactions' => $transactions,
            'filters' => ['type' => $type],
        ]);
    }
}
