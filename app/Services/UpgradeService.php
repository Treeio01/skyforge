<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Balance\DebitBalanceAction;
use App\Actions\Upgrade\CalculateChanceAction;
use App\Actions\Upgrade\GenerateRollAction;
use App\DTOs\UpgradeResultDTO;
use App\Enums\TransactionType;
use App\Enums\UpgradeResult;
use App\Enums\UserSkinSource;
use App\Enums\UserSkinStatus;
use App\Events\UpgradeCompleted;
use App\Models\ProvablyFairSeed;
use App\Models\Setting;
use App\Models\Skin;
use App\Models\Upgrade;
use App\Models\UpgradeItem;
use App\Models\User;
use App\Models\UserSkin;
use Illuminate\Support\Facades\DB;

class UpgradeService
{
    public function __construct(
        private CalculateChanceAction $calculateChance,
        private GenerateRollAction $generateRoll,
        private DebitBalanceAction $debitBalance,
    ) {}

    /**
     * @param  array<int>  $userSkinIds
     */
    public function execute(User $user, array $userSkinIds, int $balanceAmount, int $targetSkinId): UpgradeResultDTO
    {
        return DB::transaction(function () use ($user, $userSkinIds, $balanceAmount, $targetSkinId) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            // Lock and validate bet skins
            $betSkins = UserSkin::lockForUpdate()
                ->whereIn('id', $userSkinIds)
                ->where('user_id', $user->id)
                ->where('status', UserSkinStatus::Available)
                ->get();

            if ($betSkins->count() !== count($userSkinIds)) {
                throw new \DomainException('Некоторые скины недоступны для апгрейда.');
            }

            // Fetch target skin price from DB
            $targetSkin = Skin::findOrFail($targetSkinId);
            $targetPrice = $targetSkin->price;

            // Calculate total bet
            $skinsTotal = $betSkins->sum('price_at_acquisition');
            $betAmount = $skinsTotal + $balanceAmount;

            if ($betAmount <= 0) {
                throw new \DomainException('Сумма ставки должна быть больше нуля.');
            }

            if ($betAmount >= $targetPrice) {
                throw new \DomainException('Ставка должна быть меньше цены цели.');
            }

            // Debit balance portion
            if ($balanceAmount > 0) {
                $this->debitBalance->execute($user, $balanceAmount, TransactionType::UpgradeBet);
                $user->refresh();
            }

            // Burn bet skins
            $betSkins->each(fn (UserSkin $s) => $s->update(['status' => UserSkinStatus::Burned]));

            // Calculate chance
            $effectiveHouseEdge = $user->house_edge_override !== null
                ? (float) $user->house_edge_override
                : (float) (Setting::get('house_edge', 5.00));

            $chanceResult = $this->calculateChance->execute(
                betAmount: $betAmount,
                targetPrice: $targetPrice,
                houseEdge: $effectiveHouseEdge,
                chanceModifier: (float) $user->chance_modifier,
                minChance: (float) (Setting::get('min_upgrade_chance', 1.00)),
                maxChance: (float) (Setting::get('max_upgrade_chance', 95.00)),
            );

            // Generate roll
            /** @var ProvablyFairSeed $seedPair */
            $seedPair = $user->activeSeedPair ?? throw new \DomainException('Нет активной пары сидов. Обновите страницу.');
            $seedPair->increment('nonce');

            $roll = $this->generateRoll->execute(
                $seedPair->server_seed,
                $seedPair->client_seed,
                $seedPair->nonce,
            );

            // Determine result
            $isWin = $roll->value < ($chanceResult->chance / 100);
            $result = $isWin ? UpgradeResult::Win : UpgradeResult::Lose;

            // Create upgrade record
            $upgrade = Upgrade::create([
                'user_id' => $user->id,
                'target_skin_id' => $targetSkinId,
                'bet_amount' => $betAmount,
                'balance_amount' => $balanceAmount,
                'target_price' => $targetPrice,
                'chance' => $chanceResult->chance,
                'multiplier' => $chanceResult->multiplier,
                'house_edge' => $chanceResult->houseEdge,
                'chance_modifier' => $user->chance_modifier,
                'result' => $result,
                'roll_value' => $roll->value,
                'roll_hex' => $roll->hex,
                'client_seed' => $seedPair->client_seed,
                'server_seed_hash' => hash('sha256', $seedPair->server_seed),
                'server_seed_raw' => $seedPair->server_seed,
                'nonce' => $seedPair->nonce,
                'is_revealed' => false,
                'created_at' => now(),
            ]);

            // Create upgrade items
            foreach ($betSkins as $betSkin) {
                UpgradeItem::create([
                    'upgrade_id' => $upgrade->id,
                    'user_skin_id' => $betSkin->id,
                    'skin_id' => $betSkin->skin_id,
                    'price' => $betSkin->price_at_acquisition,
                ]);
            }

            // Award target skin on win
            if ($isWin) {
                UserSkin::create([
                    'user_id' => $user->id,
                    'skin_id' => $targetSkinId,
                    'price_at_acquisition' => $targetPrice,
                    'source' => UserSkinSource::UpgradeWin,
                    'source_id' => $upgrade->id,
                    'status' => UserSkinStatus::Available,
                ]);
            }

            $upgrade->load(['user', 'targetSkin']);
            UpgradeCompleted::dispatch($upgrade);

            return new UpgradeResultDTO(upgrade: $upgrade);
        });
    }
}
