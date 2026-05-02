<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Withdrawal\CreateWithdrawalData;
use App\Services\WithdrawalService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class WithdrawalController extends Controller
{
    public function store(CreateWithdrawalData $data, WithdrawalService $service): RedirectResponse
    {
        try {
            $service->create(request()->user(), $data);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Вывод создан. Trade offer будет отправлен.');
    }
}
