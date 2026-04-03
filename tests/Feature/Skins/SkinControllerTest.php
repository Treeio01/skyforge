<?php

declare(strict_types=1);

use App\Models\Skin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('returns paginated skin list', function () {
    Skin::factory()->count(3)->create();

    $this->getJson(route('skins.index'))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'market_hash_name', 'price', 'rarity_color', 'exterior', 'category']],
            'links',
            'meta',
        ]);
});

it('filters skins by category', function () {
    Skin::factory()->create(['category' => 'weapon']);
    Skin::factory()->create(['category' => 'knife']);

    $this->getJson(route('skins.index', ['category' => 'weapon']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('sorts skins by price', function () {
    Skin::factory()->create(['price' => 500]);
    Skin::factory()->create(['price' => 100]);
    Skin::factory()->create(['price' => 300]);

    $response = $this->getJson(route('skins.index', ['sort' => 'price', 'direction' => 'asc']))
        ->assertSuccessful();

    $prices = collect($response->json('data'))->pluck('price')->all();
    expect($prices)->toBe([100, 300, 500]);
});

it('searches skins by name via fulltext', function () {
    Skin::factory()->create(['market_hash_name' => 'AK-47 | Redline (Field-Tested) 001']);
    Skin::factory()->create(['market_hash_name' => 'M4A4 | Asiimov (Field-Tested) 002']);

    // FULLTEXT index requires committed data in MySQL — use LIKE fallback in test
    $this->getJson(route('skins.search', ['q' => 'Redline']))
        ->assertSuccessful();
})->skip('FULLTEXT requires committed data outside transaction');

it('returns empty for short search query', function () {
    $this->getJson(route('skins.search', ['q' => 'A']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('excludes inactive skins from catalog', function () {
    Skin::factory()->create(['is_active' => true]);
    Skin::factory()->inactive()->create();

    $this->getJson(route('skins.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});
