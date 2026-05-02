<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Deposit\CreateDepositData;
use App\Services\DepositService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepositController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Deposit/Create');
    }

    public function config(Request $request, DepositService $service): JsonResponse
    {
        return response()->json($service->depositConfig($request->user()));
    }

    public function store(CreateDepositData $data, DepositService $service): RedirectResponse
    {
        try {
            $service->initiate(request()->user(), $data);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Депозит создан.');
    }

    public function webhook(Request $request, DepositService $service): JsonResponse
    {
        $result = $service->handleWebhook($request);

        return response()->json($result['body'], $result['status']);
    }
}
