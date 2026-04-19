<?php

declare(strict_types=1);

namespace App\Actions\Deposit;

use App\Contracts\PaymentProviderInterface;
use App\Enums\DepositMethod;
use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\User;

class CreateDepositAction
{
    private const MAX_PENDING_DEPOSITS = 5;

    public function __construct(
        private PaymentProviderInterface $paymentProvider,
    ) {}

    public function execute(User $user, int $amount, DepositMethod $method): Deposit
    {
        $pendingCount = $user->deposits()->where('status', DepositStatus::Pending)->count();

        if ($pendingCount >= self::MAX_PENDING_DEPOSITS) {
            throw new \DomainException('Достигнут лимит ожидающих пополнений. Дождитесь завершения предыдущих.');
        }

        $payment = $this->paymentProvider->createPayment($amount, $method->value);

        return Deposit::create([
            'user_id' => $user->id,
            'method' => $method,
            'amount' => $amount,
            'status' => DepositStatus::Pending,
            'provider_id' => $payment->providerId,
            'idempotency_key' => $payment->providerId,
        ]);
    }
}
