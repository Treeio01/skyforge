<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Withdrawal\CreateWithdrawalRecordAction;
use App\Actions\Withdrawal\LockAvailableUserSkinAction;
use App\Actions\Withdrawal\MarkSkinAsWithdrawnAction;
use App\Actions\Withdrawal\ValidatePendingWithdrawalsLimitAction;
use App\Data\Withdrawal\CreateWithdrawalData;
use App\Jobs\ProcessWithdrawalJob;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    public function __construct(
        private ValidatePendingWithdrawalsLimitAction $validateLimit,
        private LockAvailableUserSkinAction $lockSkin,
        private MarkSkinAsWithdrawnAction $markWithdrawn,
        private CreateWithdrawalRecordAction $createRecord,
    ) {}

    public function create(User $user, CreateWithdrawalData $data): Withdrawal
    {
        $withdrawal = DB::transaction(function () use ($user, $data) {
            $this->validateLimit->execute($user);

            $userSkin = $this->lockSkin->execute($user, $data->user_skin_id);
            $this->markWithdrawn->execute($userSkin);

            return $this->createRecord->execute($user, $userSkin);
        });

        ProcessWithdrawalJob::dispatch($withdrawal);

        return $withdrawal;
    }
}
