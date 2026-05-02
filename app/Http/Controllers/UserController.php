<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\UpdateTradeUrlAction;
use App\Data\Profile\SellSkinsData;
use App\Data\Profile\UpdateTradeUrlData;
use App\Data\Promo\RedeemPromoData;
use App\Exceptions\NoSkinsToSellException;
use App\Exceptions\PromoCodeException;
use App\Services\MarketService;
use App\Services\PromoCodeService;
use App\Services\UserProfileService;
use App\Support\Admin\MoneyFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function show(Request $request, UserProfileService $service): Response
    {
        return Inertia::render('Profile/Show', $service->profileData($request->user(), $request));
    }

    public function updateTradeUrl(UpdateTradeUrlData $data, UpdateTradeUrlAction $action): RedirectResponse
    {
        $action->execute(request()->user(), $data);

        return back()->with('success', 'Trade URL обновлён.');
    }

    public function sellSkins(SellSkinsData $data, MarketService $service): RedirectResponse
    {
        try {
            $total = $service->sellSkins(request()->user(), $data);
        } catch (NoSkinsToSellException $e) {
            return back()->withErrors(['ids' => $e->getMessage()]);
        }

        return back()->with('success', 'Продажа завершена на сумму '.MoneyFormatter::format($total));
    }

    public function deposits(Request $request, UserProfileService $service): JsonResponse
    {
        return response()->json($service->recentDeposits($request->user()));
    }

    public function redeemPromo(RedeemPromoData $data, PromoCodeService $service): RedirectResponse
    {
        try {
            $result = $service->redeem(request()->user(), $data);
        } catch (PromoCodeException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        $msg = $result['type'] === 'deposit_bonus'
            ? "Промокод применён! +{$result['amount']}% к следующему пополнению."
            : 'Промокод применён! +'.MoneyFormatter::format($result['amount']);

        return back()->with('success', $msg);
    }

    public function history(Request $request, UserProfileService $service): Response
    {
        return Inertia::render('Profile/History', [
            'transactions' => $service->transactionHistory($request->user(), (string) $request->input('type', 'all')),
            'filters' => ['type' => $request->input('type', 'all')],
        ]);
    }
}
