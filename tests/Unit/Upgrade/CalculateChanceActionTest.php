<?php

declare(strict_types=1);

use App\Actions\Upgrade\CalculateChanceAction;

it('calculates correct chance for standard upgrade', function () {
    $result = app(CalculateChanceAction::class)->execute(
        betAmount: 50000,
        targetPrice: 100000,
        houseEdge: 5.00,
        chanceModifier: 1.000,
    );

    // (50000/100000) * (1 - 5/100) * 100 = 47.5%
    expect(round($result->chance, 2))->toBe(47.50);
    expect(round($result->multiplier, 2))->toBe(2.00);
});

it('clamps chance to min/max', function () {
    // Very low bet → chance below min
    $low = app(CalculateChanceAction::class)->execute(
        betAmount: 100,
        targetPrice: 10000000,
        houseEdge: 5.00,
        chanceModifier: 1.000,
    );
    expect($low->chance)->toBeGreaterThanOrEqual(1.00);

    // Very high bet → chance above max
    $high = app(CalculateChanceAction::class)->execute(
        betAmount: 99000,
        targetPrice: 100000,
        houseEdge: 5.00,
        chanceModifier: 1.000,
    );
    expect($high->chance)->toBeLessThanOrEqual(95.00);
});

it('applies streamer house edge override', function () {
    $result = app(CalculateChanceAction::class)->execute(
        betAmount: 50000,
        targetPrice: 100000,
        houseEdge: 0.00,
        chanceModifier: 1.000,
    );

    // (50000/100000) * (1 - 0/100) * 100 = 50.0%
    expect(round($result->chance, 2))->toBe(50.00);
});

it('applies chance modifier', function () {
    $result = app(CalculateChanceAction::class)->execute(
        betAmount: 50000,
        targetPrice: 100000,
        houseEdge: 5.00,
        chanceModifier: 1.200,
    );

    // 47.5 * 1.2 = 57.0%
    expect(round($result->chance, 2))->toBe(57.00);
});

it('returns applied house edge in result', function () {
    $result = app(CalculateChanceAction::class)->execute(
        betAmount: 50000,
        targetPrice: 100000,
        houseEdge: 3.50,
        chanceModifier: 1.000,
    );

    expect($result->houseEdge)->toBe(3.50);
});
