<?php

declare(strict_types=1);

use App\Actions\Upgrade\GenerateRollAction;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('produces the same roll for the same seed pair and nonce', function () {
    $action = app(GenerateRollAction::class);

    $serverSeed = str_repeat('a', 64);
    $clientSeed = 'client_seed_xyz';
    $nonce = 42;

    $first = $action->execute($serverSeed, $clientSeed, $nonce);
    $second = $action->execute($serverSeed, $clientSeed, $nonce);

    expect($first->value)->toBe($second->value);
    expect($first->value)->toBeGreaterThanOrEqual(0.0);
    expect($first->value)->toBeLessThan(100.0);
});

it('produces different rolls for different nonces', function () {
    $action = app(GenerateRollAction::class);

    $serverSeed = str_repeat('b', 64);
    $clientSeed = 'client_seed_xyz';

    $rolls = collect(range(1, 5))
        ->map(fn (int $n) => $action->execute($serverSeed, $clientSeed, $n)->value)
        ->unique();

    expect($rolls->count())->toBe(5);
});
