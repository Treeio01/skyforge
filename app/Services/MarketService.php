<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Balance\CreditBalanceAction;
use App\Actions\Balance\DebitBalanceAction;
use App\Actions\Inventory\AddSkinToInventoryAction;
use App\Actions\Inventory\CalculateSellPriceAction;
use App\Actions\Inventory\MarkSkinsBurnedAction;
use App\Actions\Skin\LoadActiveSkinsAction;
use App\Data\Profile\SellSkinsData;
use App\Data\Skin\BuySkinsData;
use App\Enums\TransactionType;
use App\Enums\UserSkinSource;
use App\Enums\UserSkinStatus;
use App\Exceptions\NoSkinsToSellException;
use App\Exceptions\SkinNotAvailableException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarketService
{
    public function __construct(
        private CalculateSellPriceAction $calculatePrice,
        private MarkSkinsBurnedAction $markBurned,
        private CreditBalanceAction $creditBalance,
        private LoadActiveSkinsAction $loadActiveSkins,
        private DebitBalanceAction $debitBalance,
        private AddSkinToInventoryAction $addToInventory,
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

    /** @return array{count: int, total: int} */
    public function buy(User $user, BuySkinsData $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            $skins = $this->loadActiveSkins->execute($data->skin_ids);

            if ($skins->count() !== count($data->skin_ids)) {
                throw new SkinNotAvailableException('Некоторые скины недоступны');
            }

            $totalPrice = (int) $skins->sum('price');

            $this->debitBalance->execute(
                $user,
                $totalPrice,
                TransactionType::Withdrawal,
                description: 'Покупка '.$skins->count().' скинов на рынке',
            );

            foreach ($skins as $skin) {
                $this->addToInventory->execute($user, $skin, UserSkinSource::Deposit);
            }

            return ['count' => $skins->count(), 'total' => $totalPrice];
        });
    }
}
