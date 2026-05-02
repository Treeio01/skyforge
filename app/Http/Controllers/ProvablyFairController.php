<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProvablyFair\GenerateSeedPairAction;
use App\Models\FaqCategory;
use App\Models\FaqItem;
use App\Models\ProvablyFairSeed;
use App\Models\Upgrade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProvablyFairController extends Controller
{
    public function index(Request $request): Response
    {
        $seedPair = $request->user()?->activeSeedPair;

        $categories = FaqCategory::active()
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'name', 'name_en']);

        // Pass both languages so the React side can pick by i18n.language.
        // Fallbacks to RU when the EN translation is missing.
        $faqItems = FaqItem::active()
            ->with('faqCategory')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($item) => $item->faqCategory?->slug ?? $item->category ?? 'other')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'question' => $item->question,
                'answer' => $item->answer,
                'question_en' => $item->question_en,
                'answer_en' => $item->answer_en,
            ])->values());

        return Inertia::render('ProvablyFair/Index', [
            'seedPair' => $seedPair ? [
                'client_seed' => $seedPair->client_seed,
                'server_seed_hash' => $seedPair->server_seed_hash,
                'nonce' => $seedPair->nonce,
            ] : null,
            'categories' => $categories,
            'faq' => $faqItems,
        ]);
    }

    public function updateClientSeed(Request $request, GenerateSeedPairAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'client_seed' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();
        /** @var ProvablyFairSeed|null $oldSeed */
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
