<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Balance\DebitBalanceAction;
use App\Actions\Inventory\AddSkinToInventoryAction;
use App\Actions\Inventory\MarkSkinsBurnedAction;
use App\Actions\Upgrade\CalculateChanceAction;
use App\Actions\Upgrade\CreateUpgradeRecordAction;
use App\Actions\Upgrade\GenerateRollAction;
use App\Actions\Upgrade\IncrementSeedNonceAction;
use App\Actions\Upgrade\LockBetSkinsAction;
use App\Actions\Upgrade\RecordUpgradeItemsAction;
use App\Actions\Upgrade\ValidateUpgradeBetAction;
use App\Data\Upgrade\CreateUpgradeData;
use App\DTOs\UpgradeResultDTO;
use App\Enums\TransactionType;
use App\Enums\UpgradeResult;
use App\Enums\UserSkinSource;
use App\Events\UpgradeCompleted;
use App\Models\Setting;
use App\Models\Skin;
use App\Models\User;
use App\Models\UserSkin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UpgradeService
{
    public function __construct(
        private LockBetSkinsAction $lockBetSkins,
        private ValidateUpgradeBetAction $validateBet,
        private CalculateChanceAction $calculateChance,
        private IncrementSeedNonceAction $bumpNonce,
        private GenerateRollAction $generateRoll,
        private CreateUpgradeRecordAction $createUpgrade,
        private RecordUpgradeItemsAction $recordItems,
        private DebitBalanceAction $debitBalance,
        private MarkSkinsBurnedAction $burnSkins,
        private AddSkinToInventoryAction $addSkin,
    ) {}

    public function execute(User $user, CreateUpgradeData $data): UpgradeResultDTO
    {
        return DB::transaction(function () use ($user, $data) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            $betSkins = $this->lockBetSkins->execute($user, $data->user_skin_ids);
            $targetSkin = Skin::findOrFail($data->target_skin_id);

            $betAmount = $this->computeBetAmount($betSkins, $data->balance_amount);
            $this->validateBet->execute($betAmount, (int) $targetSkin->price);

            if ($data->balance_amount > 0) {
                $this->debitBalance->execute($user, $data->balance_amount, TransactionType::UpgradeBet);
                $user->refresh();
            }

            $this->burnSkins->execute($betSkins);

            $chance = $this->calculateChance->execute(
                betAmount: $betAmount,
                targetPrice: (int) $targetSkin->price,
                houseEdge: $this->effectiveHouseEdge($user),
                chanceModifier: (float) $user->chance_modifier,
                minChance: (float) Setting::get('min_upgrade_chance', 1.00),
                maxChance: (float) Setting::get('max_upgrade_chance', 95.00),
            );

            $seed = $this->bumpNonce->execute($user);
            $roll = $this->generateRoll->execute($seed->server_seed, $seed->client_seed, $seed->nonce);
            $isWin = $roll->value < ($chance->chance / 100);
            $result = $isWin ? UpgradeResult::Win : UpgradeResult::Lose;

            $upgrade = $this->createUpgrade->execute(
                user: $user,
                targetSkinId: $data->target_skin_id,
                targetPrice: (int) $targetSkin->price,
                betAmount: $betAmount,
                balanceAmount: $data->balance_amount,
                chance: $chance,
                roll: $roll,
                seed: $seed,
                result: $result,
            );

            $this->recordItems->execute($upgrade, $betSkins);

            if ($isWin) {
                $this->addSkin->execute(
                    user: $user,
                    skin: $targetSkin,
                    source: UserSkinSource::UpgradeWin,
                    priceAtAcquisition: (int) $targetSkin->price,
                    sourceId: $upgrade->id,
                );
            }

            $upgrade->load(['user', 'targetSkin']);
            UpgradeCompleted::dispatch($upgrade);

            return new UpgradeResultDTO(upgrade: $upgrade);
        });
    }

    /** @param Collection<int, UserSkin> $betSkins */
    private function computeBetAmount(Collection $betSkins, int $balanceAmount): int
    {
        $skinsTotal = (int) $betSkins->sum(
            fn (UserSkin $us) => $us->skin?->price > 0 ? (int) $us->skin->price : (int) $us->price_at_acquisition,
        );

        return $skinsTotal + $balanceAmount;
    }

    private function effectiveHouseEdge(User $user): float
    {
        return $user->house_edge_override !== null
            ? (float) $user->house_edge_override
            : (float) Setting::get('house_edge', 5.00);
    }
}
