<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\DepositStatus;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DepositActionsController extends Controller
{
    public function markCompleted(Deposit $deposit): RedirectResponse
    {
        if ($deposit->status === DepositStatus::Completed) {
            return back()->with('error', 'Депозит уже завершён.');
        }

        DB::transaction(function () use ($deposit) {
            $deposit->update([
                'status' => DepositStatus::Completed,
                'completed_at' => now(),
            ]);
        });

        return back()->with('success', "Депозит #{$deposit->id} помечен как завершённый.");
    }
}
