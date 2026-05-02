<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FaqCategory;
use App\Models\FaqItem;
use App\Models\Upgrade;
use App\Models\User;

class ProvablyFairService
{
    /** @return array<string, mixed> */
    public function pageData(?User $user): array
    {
        $seedPair = $user?->activeSeedPair;

        return [
            'seedPair' => $seedPair ? [
                'client_seed' => $seedPair->client_seed,
                'server_seed_hash' => $seedPair->server_seed_hash,
                'nonce' => $seedPair->nonce,
            ] : null,
            'categories' => FaqCategory::query()->active()
                ->orderBy('sort_order')
                ->get(['id', 'slug', 'name', 'name_en']),
            'faq' => $this->faqTree(),
        ];
    }

    /** @return array<string, mixed> */
    public function verifyData(Upgrade $upgrade): array
    {
        return [
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
        ];
    }

    /** @return array<string, mixed> */
    private function faqTree(): array
    {
        return FaqItem::query()->active()
            ->with('faqCategory')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($item) => $item->faqCategory?->slug ?? $item->category ?? 'other')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'question' => $item->question,
                'answer' => $item->answer,
                'question_en' => $item->question_en,
                'answer_en' => $item->answer_en,
            ])->values())
            ->all();
    }
}
