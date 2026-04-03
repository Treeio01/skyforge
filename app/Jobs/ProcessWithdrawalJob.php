<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\TradeProviderInterface;
use App\Enums\UserSkinStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWithdrawalJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public Withdrawal $withdrawal,
    ) {
        $this->onQueue('payments');
    }

    public function handle(TradeProviderInterface $tradeProvider): void
    {
        $withdrawal = $this->withdrawal;

        $withdrawal->update(['status' => WithdrawalStatus::Processing]);

        $tradeOffer = $tradeProvider->sendTradeOffer(
            $withdrawal->user->trade_url,
            $withdrawal->skin->market_hash_name,
        );

        $withdrawal->update([
            'trade_offer_id' => $tradeOffer->tradeOfferId,
            'trade_offer_status' => $tradeOffer->status,
            'status' => WithdrawalStatus::Sent,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->withdrawal->update([
            'status' => WithdrawalStatus::Failed,
            'failure_reason' => $exception->getMessage(),
        ]);

        // Return skin to user inventory
        $this->withdrawal->userSkin->update([
            'status' => UserSkinStatus::Available,
        ]);
    }
}
