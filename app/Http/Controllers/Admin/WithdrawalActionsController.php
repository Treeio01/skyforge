<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WithdrawalActionsController extends Controller
{
    public function approve(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        if (! \in_array($withdrawal->status, [WithdrawalStatus::Pending, WithdrawalStatus::Processing], true)) {
            return back()->with('error', 'Только pending/processing выводы можно подтвердить.');
        }

        $withdrawal->update([
            'status' => WithdrawalStatus::Completed,
            'completed_at' => now(),
        ]);

        return back()->with('success', "Вывод #{$withdrawal->id} подтверждён.");
    }

    public function reject(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($withdrawal->status === WithdrawalStatus::Completed) {
            return back()->with('error', 'Завершённый вывод нельзя отклонить.');
        }

        $withdrawal->update([
            'status' => WithdrawalStatus::Cancelled,
            'failure_reason' => $data['reason'] ?? null,
        ]);

        return back()->with('success', "Вывод #{$withdrawal->id} отклонён.");
    }
}
