<?php

declare(strict_types=1);

use App\Models\ProvablyFairSeed;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(LazilyRefreshDatabase::class);

function fakeSteamUser(string $steamId = '76561198000000001', string $nickname = 'TestPlayer', string $avatar = 'https://example.com/avatar.jpg'): SocialiteUser
{
    $user = new SocialiteUser;
    $user->map([
        'id' => $steamId,
        'nickname' => $nickname,
        'avatar' => $avatar,
    ]);

    return $user;
}

it('redirects to steam openid', function () {
    $this->get(route('auth.steam'))
        ->assertRedirect();
});

it('creates a new user on first steam callback', function () {
    Socialite::shouldReceive('driver->user')
        ->andReturn(fakeSteamUser());

    $this->get('/auth/steam/callback')
        ->assertRedirect(route('home'));

    $this->assertAuthenticated();

    $user = User::where('steam_id', '76561198000000001')->first();
    expect($user)
        ->not->toBeNull()
        ->username->toBe('TestPlayer')
        ->avatar_url->toBe('https://example.com/avatar.jpg');
});

it('generates provably fair seed for new user', function () {
    Socialite::shouldReceive('driver->user')
        ->andReturn(fakeSteamUser());

    $this->get('/auth/steam/callback');

    $user = User::where('steam_id', '76561198000000001')->first();
    $seed = ProvablyFairSeed::where('user_id', $user->id)->where('is_active', true)->first();

    expect($seed)
        ->not->toBeNull()
        ->nonce->toBe(0)
        ->client_seed->not->toBeEmpty()
        ->server_seed->not->toBeEmpty();
});

it('updates avatar and username on repeat login', function () {
    $user = User::factory()->create([
        'steam_id' => '76561198000000001',
        'username' => 'OldName',
        'avatar_url' => 'https://old.com/avatar.jpg',
    ]);

    Socialite::shouldReceive('driver->user')
        ->andReturn(fakeSteamUser('76561198000000001', 'NewName', 'https://new.com/avatar.jpg'));

    $this->get('/auth/steam/callback');

    $user->refresh();
    expect($user)
        ->username->toBe('NewName')
        ->avatar_url->toBe('https://new.com/avatar.jpg');
});

it('blocks banned user from logging in', function () {
    User::factory()->banned()->create([
        'steam_id' => '76561198000000001',
    ]);

    Socialite::shouldReceive('driver->user')
        ->andReturn(fakeSteamUser());

    $this->get('/auth/steam/callback')
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');

    $this->assertGuest();
});

it('handles steam api failure gracefully', function () {
    Socialite::shouldReceive('driver->user')
        ->andThrow(new Exception('Steam unavailable'));

    $this->get('/auth/steam/callback')
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');

    $this->assertGuest();
});

it('logs out authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

it('requires auth to logout', function () {
    $this->post(route('logout'))
        ->assertRedirect(route('auth.steam'));
});
