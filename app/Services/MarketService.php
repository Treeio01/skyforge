<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Balance\CreditBalanceAction;
use App\Actions\Inventory\CalculateSellPriceAction;
use App\Actions\Inventory\MarkSkinsBurnedAction;
use App\Data\Profile\SellSkinsData;
use App\Enums\TransactionType;
use App\Enums\UserSkinStatus;
use App\Exceptions\NoSkinsToSellException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarketService
{
    public function __construct(
        private CalculateSellPriceAction $calculatePrice,
        private MarkSkinsBurnedAction $markBurned,
        private CreditBalanceAction $creditBalance,
    ) {}

    public function sellSkins(User $user, SellSkinsData $data): int
    {
        return DB::transaction(function () use ($user, $data) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            $query = $user->userSkins()
                ->where('status', UserSkinStatus::Available)
                ->with('skin');

            if ($data->mode === 'selected') {
                $ids = $data->ids ?? [];
                if ($ids === []) {
                    throw new NoSkinsToSellException('Не выбраны скины');
                }
                $query->whereIn('id', $ids);
            }

            $skins = $query->get();
            if ($skins->isEmpty()) {
                throw new NoSkinsToSellException('Нет скинов для продажи');
            }

            $totalAmount = $this->calculatePrice->execute($skins);
            $count = $this->markBurned->execute($skins);

            $this->creditBalance->execute(
                $user,
                $totalAmount,
                TransactionType::Bonus,
                description: "Продажа {$count} скинов",
            );

            return $totalAmount;
        });
    }
}
