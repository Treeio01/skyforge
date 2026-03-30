<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('shows profile page for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertSuccessful();
});

it('redirects guest from profile', function () {
    $this->get(route('profile'))
        ->assertRedirect(route('auth.steam'));
});

it('updates trade url with valid url', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.trade-url'), [
            'trade_url' => 'https://steamcommunity.com/tradeoffer/new/?partner=123456&token=abcDEF_-',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->refresh()->trade_url)
        ->toBe('https://steamcommunity.com/tradeoffer/new/?partner=123456&token=abcDEF_-');
});

it('rejects invalid trade url', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.trade-url'), [
            'trade_url' => 'https://example.com/not-a-trade-url',
        ])
        ->assertSessionHasErrors('trade_url');
});

it('shows transaction history', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.history'))
        ->assertSuccessful();
});
