<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Enums\UserSkinStatus;
use App\Http\Resources\SkinBriefResource;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class UserProfileService
{
    /** @return array<string, mixed> */
    public function profilePayload(User $user, Request $request): array
    {
        return (new UserProfileResource($user))->resolve($request);
    }

    /** @return array<string, mixed> */
    public function profileData(User $user, Request $request): array
    {
        return [
            'profile' => $this->profilePayload($user, $request),
            'inventory' => $this->mapInventory($user, $request),
            'recentUpgrades' => $this->recentUpgradesFor($user),
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function recentDeposits(User $user, int $limit = 50): Collection
    {
        return $user->transactions()
            ->where('type', TransactionType::Deposit)
            ->orderByDesc('created_at')
            ->take($limit)
            ->get(['id', 'amount', 'balance_after', 'description', 'created_at']);
    }

    public function transactionHistory(User $user, string $type = 'all'): LengthAwarePaginator
    {
        $valid = array_column(TransactionType::cases(), 'value');
        $effectiveType = ($type !== 'all' && in_array($type, $valid, true)) ? $type : 'all';

        return $user->transactions()
            ->when($effectiveType !== 'all', fn ($q) => $q->where('type', $effectiveType))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();
    }

    /** @return array<int, array<string, mixed>> */
    public function inventoryFor(User $user, Request $request): array
    {
        return $this->mapInventory($user, $request);
    }

    /** @return array<int, array<string, mixed>> */
    public function recentUpgradesFor(User $user): array
    {
        return $this->mapRecentUpgrades($user);
    }

    /** @return array<int, array<string, mixed>> */
    private function mapInventory(User $user, Request $request): array
    {
        return $user->userSkins()
            ->where('status', UserSkinStatus::Available)
            ->with('skin')
            ->get()
            ->map(fn ($us) => [
                'id' => $us->id,
                'skin' => (new SkinBriefResource($us->skin))->resolve($request),
                'price_at_acquisition' => $us->price_at_acquisition,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function mapRecentUpgrades(User $user): array
    {
        return $user->upgrades()
            ->with(['targetSkin', 'items.skin'])
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function ($u) {
                $betSkin = $u->items->first()?->skin;

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
            })
            ->all();
    }
}
