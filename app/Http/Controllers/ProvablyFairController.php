<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProvablyFair\GenerateSeedPairAction;
use App\Models\Upgrade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProvablyFairController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\ProvablyFairSeed|null $seedPair */
        $seedPair = $request->user()->activeSeedPair;

        return Inertia::render('ProvablyFair/Index', [
            'seedPair' => $seedPair ? [
                'client_seed' => $seedPair->client_seed,
                'server_seed_hash' => $seedPair->server_seed_hash,
                'nonce' => $seedPair->nonce,
            ] : null,
        ]);
    }

    public function updateClientSeed(Request $request, GenerateSeedPairAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'client_seed' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();
        /** @var \App\Models\ProvablyFairSeed|null $oldSeed */
        $oldSeed = $user->activeSeedPair;

        if ($oldSeed) {
            $oldSeed->update(['is_active' => false]);
        }

        $newSeed = $action->execute($user);
        $newSeed->update(['client_seed' => $validated['client_seed']]);

        return back()->with('success', 'Seed обновлён.')
            ->with('revealed_seed', $oldSeed?->server_seed);
    }

    public function verify(Upgrade $upgrade): Response
    {
        return Inertia::render('ProvablyFair/Verify', [
            'upgrade' => [
                'id' => $upgrade->id,
                'client_seed' => $upgrade->client_seed,
                'server_seed_hash' => $upgrade->server_seed_hash,
                'server_seed_raw' => $upgrade->is_revealed ? $upgrade->server_seed_raw : null,
                'nonce' => $upgrade->nonce,
                'roll_value' => $upgrade->roll_value,
                'roll_hex' => $upgrade->roll_hex,
                'chance' => $upgrade->chance,
                'result' => $upgrade->result,
            ],
        ]);
    }
}
