<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProvablyFair\RotateClientSeedAction;
use App\Data\ProvablyFair\RotateClientSeedData;
use App\Models\Upgrade;
use App\Services\ProvablyFairService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProvablyFairController extends Controller
{
    public function index(Request $request, ProvablyFairService $service): Response
    {
        return Inertia::render('ProvablyFair/Index', $service->pageData($request->user()));
    }

    public function updateClientSeed(RotateClientSeedData $data, RotateClientSeedAction $action): RedirectResponse
    {
        $result = $action->execute(request()->user(), $data->client_seed);

        return back()->with('success', 'Seed обновлён.')->with('revealed_seed', $result['revealed']);
    }

    public function verify(Upgrade $upgrade, ProvablyFairService $service): Response
    {
        return Inertia::render('ProvablyFair/Verify', $service->verifyData($upgrade));
    }
}
