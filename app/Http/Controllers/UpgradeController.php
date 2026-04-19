<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UpgradeResult;
use App\Enums\UserSkinStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Http\Resources\SkinBriefResource;
use App\Services\UpgradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UpgradeController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $inventory = $user
            ? $user->userSkins()
                ->where('status', UserSkinStatus::Available)
                ->with('skin')
                ->get()
                ->map(fn ($us) => [
                    'id' => $us->id,
                    'skin' => (new SkinBriefResource($us->skin))->resolve($request),
                    'price_at_acquisition' => $us->price_at_acquisition,
                ])
            : collect();

        return Inertia::render('Upgrade/Index', [
            'inventory' => $inventory,
            'balance' => $user?->balance ?? 0,
        ]);
    }

    public function store(Request $request, UpgradeService $service): RedirectResponse
    {
        $validated = $request->validate([
            'user_skin_ids' => ['sometimes', 'array'],
            'user_skin_ids.*' => ['integer', 'exists:user_skins,id'],
            'balance_amount' => ['required', 'integer', 'min:0'],
            'target_skin_id' => ['required', 'integer', 'exists:skins,id'],
        ]);

        try {
            $result = $service->execute(
                user: $request->user(),
                userSkinIds: $validated['user_skin_ids'],
                balanceAmount: $validated['balance_amount'],
                targetSkinId: $validated['target_skin_id'],
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (InsufficientBalanceException) {
            return back()->with('error', 'Недостаточно средств.');
        }

        $win = $result->upgrade->result === UpgradeResult::Win;

        return back()->with($win ? 'success' : 'error', $win ? 'Вы выиграли!' : 'Вы проиграли.');
    }
}
