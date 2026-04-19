<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Deposit\CompleteDepositAction;
use App\Actions\Deposit\CreateDepositAction;
use App\Contracts\PaymentProviderInterface;
use App\Enums\DepositMethod;
use App\Models\Deposit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DepositController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Deposit/Create');
    }

    public function config(Request $request): JsonResponse
    {
        $rates = Cache::get('exchange_rates', [
            'RUB' => 1.0,
            'USD' => 96.0,
            'EUR' => 105.0,
            'UAH' => 2.2,
            'KZT' => 0.19,
            'BYN' => 29.0,
            'USDT' => 96.0,
            'TON' => 340.0,
            'TRX' => 24.0,
        ]);

        $bonus = null;
        $user = $request->user();

        if ($user) {
            // Ищем deposit_bonus промокод, который юзер активировал (ввёл)
            $usage = $user->promoCodeUsages()
                ->whereHas('promoCode', fn ($q) => $q->where('type', 'deposit_bonus'))
                ->with('promoCode')
                ->latest('created_at')
                ->first();

            if ($usage && $usage->promoCode) {
                $bonus = [
                    'code' => $usage->promoCode->code,
                    'percent' => $usage->promoCode->amount,
                ];
            }
        }

        return response()->json([
            'rates' => $rates,
            'updated_at' => Cache::get('exchange_rates_updated_at'),
            'bonus' => $bonus,
        ]);
    }

    public function store(Request $request, CreateDepositAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:'.config('skyforge.min_bet_amount'), 'max:'.config('skyforge.max_bet_amount')],
            'method' => ['required', 'string', 'in:sbp,crypto'],
        ]);

        try {
            $deposit = $action->execute(
                $request->user(),
                (int) $validated['amount'],
                DepositMethod::from($validated['method']),
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Депозит создан.');
    }

    public function webhook(Request $request, PaymentProviderInterface $provider, CompleteDepositAction $action): JsonResponse
    {
        $webhook = $provider->verifyWebhook($request);

        if (! $webhook->signatureValid) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $deposit = Deposit::where('provider_id', $webhook->providerId)->first();

        if (! $deposit) {
            return response()->json(['error' => 'Deposit not found'], 404);
        }

        $action->execute($deposit);

        return response()->json(['ok' => true]);
    }
}
