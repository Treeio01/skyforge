<?php

declare(strict_types=1);

namespace App\Actions\Upgrade;

use App\DTOs\ChanceResultDTO;
use App\DTOs\RollResultDTO;
use App\Enums\UpgradeResult;
use App\Models\ProvablyFairSeed;
use App\Models\Upgrade;
use App\Models\User;

class CreateUpgradeRecordAction
{
    public function execute(
        User $user,
        int $targetSkinId,
        int $targetPrice,
        int $betAmount,
        int $balanceAmount,
        ChanceResultDTO $chance,
        RollResultDTO $roll,
        ProvablyFairSeed $seed,
        UpgradeResult $result,
    ): Upgrade {
        return Upgrade::create([
            'user_id' => $user->id,
            'target_skin_id' => $targetSkinId,
            'bet_amount' => $betAmount,
            'balance_amount' => $balanceAmount,
            'target_price' => $targetPrice,
            'chance' => $chance->chance,
            'multiplier' => $chance->multiplier,
            'house_edge' => $chance->houseEdge,
            'chance_modifier' => $user->chance_modifier,
            'result' => $result,
            'roll_value' => $roll->value,
            'roll_hex' => $roll->hex,
            'client_seed' => $seed->client_seed,
            'server_seed_hash' => hash('sha256', $seed->server_seed),
            'server_seed_raw' => $seed->server_seed,
            'nonce' => $seed->nonce,
            'is_revealed' => false,
            'created_at' => now(),
        ]);
    }
}
