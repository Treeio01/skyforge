<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Upgrade\CreateUpgradeData;
use App\Enums\UpgradeResult;
use App\Exceptions\InsufficientBalanceException;
use App\Services\UpgradeService;
use App\Services\UserProfileService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UpgradeController extends Controller
{
    public function index(Request $request, UserProfileService $service): Response
    {
        $user = $request->user();

        return Inertia::render('Upgrade/Index', [
            'inventory' => $user ? $service->inventoryFor($user, $request) : [],
            'balance' => $user?->balance ?? 0,
        ]);
    }

    public function store(CreateUpgradeData $data, UpgradeService $service): RedirectResponse
    {
        try {
            $result = $service->execute(request()->user(), $data);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (InsufficientBalanceException) {
            return back()->with('error', 'Недостаточно средств.');
        }

        $win = $result->upgrade->result === UpgradeResult::Win;

        return back()->with($win ? 'success' : 'error', $win ? 'Вы выиграли!' : 'Вы проиграли.');
    }
}
