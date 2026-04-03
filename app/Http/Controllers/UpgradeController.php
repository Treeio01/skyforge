<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UpgradeResult;
use App\Exceptions\InsufficientBalanceException;
use App\Services\UpgradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpgradeController extends Controller
{
    public function store(Request $request, UpgradeService $service): RedirectResponse
    {
        $validated = $request->validate([
            'user_skin_ids' => ['required', 'array', 'min:1'],
            'user_skin_ids.*' => ['required', 'integer', 'exists:user_skins,id'],
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

        $win = $result->upgrade->result === UpgradeResult::Win; // @phpstan-ignore identical.alwaysFalse

        return back()->with($win ? 'success' : 'error', $win ? 'Вы выиграли!' : 'Вы проиграли.'); // @phpstan-ignore ternary.alwaysFalse, ternary.alwaysFalse
    }
}
