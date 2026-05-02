<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Deposit\CompleteDepositAction;
use App\Actions\Deposit\CreateDepositAction;
use App\Contracts\PaymentProviderInterface;
use App\Data\Deposit\CreateDepositData;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DepositService
{
    private const DEFAULT_RATES = [
        'RUB' => 1.0,
        'USD' => 96.0,
        'EUR' => 105.0,
        'UAH' => 2.2,
        'KZT' => 0.19,
        'BYN' => 29.0,
        'USDT' => 96.0,
        'TON' => 340.0,
        'TRX' => 24.0,
    ];

    public function __construct(
        private CreateDepositAction $createDeposit,
        private CompleteDepositAction $completeDeposit,
        private PaymentProviderInterface $paymentProvider,
    ) {}

    public function initiate(User $user, CreateDepositData $data): Deposit
    {
        return $this->createDeposit->execute($user, $data->amount, $data->method);
    }

    /** @return array{status: int, body: array<string, mixed>} */
    public function handleWebhook(Request $request): array
    {
        $webhook = $this->paymentProvider->verifyWebhook($request);

        if (! $webhook->signatureValid) {
            return ['status' => 403, 'body' => ['error' => 'Invalid signature']];
        }

        $deposit = Deposit::query()->where('provider_id', $webhook->providerId)->first();

        if (! $deposit) {
            return ['status' => 404, 'body' => ['error' => 'Deposit not found']];
        }

        $this->completeDeposit->execute($deposit);

        return ['status' => 200, 'body' => ['ok' => true]];
    }

    /** @return array<string, mixed> */
    public function depositConfig(?User $user): array
    {
        return [
            'rates' => Cache::get('exchange_rates', self::DEFAULT_RATES),
            'updated_at' => Cache::get('exchange_rates_updated_at'),
            'bonus' => $user ? $this->resolveBonus($user) : null,
        ];
    }

    /** @return array{code: string, percent: int}|null */
    private function resolveBonus(User $user): ?array
    {
        $usage = $user->promoCodeUsages()
            ->whereHas('promoCode', fn ($q) => $q->where('type', 'deposit_bonus'))
            ->with('promoCode')
            ->latest('created_at')
            ->first();

        if (! $usage || ! $usage->promoCode) {
            return null;
        }

        return [
            'code' => $usage->promoCode->code,
            'percent' => (int) $usage->promoCode->amount,
        ];
    }
}
