<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Balance\CreditBalanceAction;
use App\Actions\Promo\RecordPromoUsageAction;
use App\Actions\Promo\ValidatePromoCodeAction;
use App\Data\Promo\RedeemPromoData;
use App\Enums\TransactionType;
use App\Exceptions\PromoCodeException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PromoCodeService
{
    public function __construct(
        private ValidatePromoCodeAction $validate,
        private RecordPromoUsageAction $recordUsage,
        private CreditBalanceAction $creditBalance,
    ) {}

    /** @return array{type: string, amount: int} */
    public function redeem(User $user, RedeemPromoData $data): array
    {
        [$promo, $error] = $this->validate->execute($user, $data->code);

        if ($error !== null) {
            throw new PromoCodeException($error);
        }

        if ($promo->type === 'deposit_bonus') {
            $this->recordUsage->execute($user, $promo);

            return ['type' => 'deposit_bonus', 'amount' => (int) $promo->amount];
        }

        return DB::transaction(function () use ($user, $promo) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            $this->creditBalance->execute(
                $user,
                (int) $promo->amount,
                TransactionType::Bonus,
                description: "Промокод: {$promo->code}",
            );

            $this->recordUsage->execute($user, $promo);

            return ['type' => 'balance', 'amount' => (int) $promo->amount];
        });
    }
}
