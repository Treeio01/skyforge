<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Withdrawal\CreateWithdrawalAction;
use App\Jobs\ProcessWithdrawalJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function store(Request $request, CreateWithdrawalAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'user_skin_id' => ['required', 'integer', 'exists:user_skins,id'],
        ]);

        try {
            $withdrawal = $action->execute($request->user(), $validated['user_skin_id']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        ProcessWithdrawalJob::dispatch($withdrawal);

        return back()->with('success', 'Вывод создан. Trade offer будет отправлен.');
    }
}
