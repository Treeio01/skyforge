<?php

declare(strict_types=1);

namespace App\Actions\Withdrawal;

use App\Enums\UserSkinStatus;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\UserSkin;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class CreateWithdrawalAction
{
    private const MAX_PENDING_WITHDRAWALS = 3;

    public function execute(User $user, int $userSkinId): Withdrawal
    {
        return DB::transaction(function () use ($user, $userSkinId) {
            $pendingCount = $user->withdrawals()
                ->whereIn('status', [WithdrawalStatus::Pending, WithdrawalStatus::Processing])
                ->count();

            if ($pendingCount >= self::MAX_PENDING_WITHDRAWALS) {
                throw new \DomainException('Достигнут лимит ожидающих выводов. Дождитесь завершения предыдущих.');
            }

            /** @var UserSkin $userSkin */
            $userSkin = UserSkin::lockForUpdate()
                ->where('id', $userSkinId)
                ->where('user_id', $user->id)
                ->where('status', UserSkinStatus::Available)
                ->firstOrFail();

            $userSkin->update(['status' => UserSkinStatus::Withdrawn]);

            return Withdrawal::create([
                'user_id' => $user->id,
                'user_skin_id' => $userSkin->id,
                'skin_id' => $userSkin->skin_id,
                'amount' => $userSkin->price_at_acquisition,
                'status' => WithdrawalStatus::Pending,
            ]);
        });
    }
}
