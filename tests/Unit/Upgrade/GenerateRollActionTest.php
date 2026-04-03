<?php

declare(strict_types=1);

use App\Actions\Upgrade\GenerateRollAction;

it('generates deterministic roll from known seeds', function () {
    $action = new GenerateRollAction;

    $result1 = $action->execute('server_seed_123', 'client_seed_abc', 1);
    $result2 = $action->execute('server_seed_123', 'client_seed_abc', 1);

    expect($result1->value)->toBe($result2->value);
    expect($result1->hex)->toBe($result2->hex);
});

it('produces different rolls for different nonces', function () {
    $action = new GenerateRollAction;

    $result1 = $action->execute('seed', 'client', 1);
    $result2 = $action->execute('seed', 'client', 2);

    expect($result1->value)->not->toBe($result2->value);
});

it('roll value is between 0 and 1', function () {
    $action = new GenerateRollAction;

    for ($i = 0; $i < 100; $i++) {
        $result = $action->execute('test_seed', 'test_client', $i);
        expect($result->value)->toBeGreaterThanOrEqual(0.0)->toBeLessThan(1.0);
    }
});

it('uses HMAC-SHA256 correctly', function () {
    $action = new GenerateRollAction;

    $serverSeed = 'known_server_seed';
    $clientSeed = 'known_client';
    $nonce = 42;

    $result = $action->execute($serverSeed, $clientSeed, $nonce);

    // Manual verification
    $hmac = hash_hmac('sha256', 'known_client-42', $serverSeed);
    $hex = substr($hmac, 0, 8);
    $int = hexdec($hex);
    $expected = $int / 0xFFFFFFFF;

    expect($result->hex)->toBe($hex);
    expect($result->value)->toBe($expected);
});
