<?php

declare(strict_types=1);

use App\Models\Skin;
use App\Models\SkinPrice;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

it('updates prices from market api', function () {
    $skin = Skin::factory()->create([
        'market_hash_name' => 'AK-47 | Redline (Field-Tested) 001',
        'price' => 1000,
    ]);

    Http::fake([
        config('skyforge.price_sync.source_url') => Http::response([
            'AK-47 | Redline (Field-Tested) 001' => ['price' => 15.00],
        ]),
    ]);

    $this->artisan('skins:sync-prices')->assertSuccessful();

    expect($skin->refresh()->price)->toBe(1500);
});

it('logs price change above threshold to skin_prices', function () {
    $skin = Skin::factory()->create([
        'market_hash_name' => 'AWP | Asiimov (Field-Tested) 001',
        'price' => 1000,
    ]);

    Http::fake([
        config('skyforge.price_sync.source_url') => Http::response([
            'AWP | Asiimov (Field-Tested) 001' => ['price' => 20.00],
        ]),
    ]);

    $this->artisan('skins:sync-prices')->assertSuccessful();

    expect(SkinPrice::where('skin_id', $skin->id)->count())->toBe(1);
    expect(SkinPrice::first()->price)->toBe(2000);
});

it('does not log small price changes', function () {
    $skin = Skin::factory()->create([
        'market_hash_name' => 'M4A4 | Howl (FN) 001',
        'price' => 10000,
    ]);

    Http::fake([
        config('skyforge.price_sync.source_url') => Http::response([
            'M4A4 | Howl (FN) 001' => ['price' => 100.50],
        ]),
    ]);

    $this->artisan('skins:sync-prices')->assertSuccessful();

    expect(SkinPrice::count())->toBe(0);
    expect($skin->refresh()->price)->toBe(10050);
});

it('handles api failure gracefully', function () {
    Http::fake([
        config('skyforge.price_sync.source_url') => Http::response(null, 500),
    ]);

    $this->artisan('skins:sync-prices')->assertFailed();
});
