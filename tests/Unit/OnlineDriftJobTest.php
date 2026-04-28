<?php

declare(strict_types=1);

use App\Jobs\OnlineDriftJob;

it('drifts within bounds', function () {
    $state = ['value' => 1550, 'direction' => 1];
    for ($i = 0; $i < 200; $i++) {
        $state = OnlineDriftJob::computeNext($state, 1500, 1600, 3);
        expect($state['value'])->toBeGreaterThanOrEqual(1500);
        expect($state['value'])->toBeLessThanOrEqual(1600);
    }
});

it('flips direction at upper bound', function () {
    $state = ['value' => 1599, 'direction' => 1];
    $next = OnlineDriftJob::computeNext($state, 1500, 1600, 3);
    expect($next['value'])->toBe(1600);
    expect($next['direction'])->toBe(-1);
});

it('flips direction at lower bound', function () {
    $state = ['value' => 1501, 'direction' => -1];
    $next = OnlineDriftJob::computeNext($state, 1500, 1600, 3);
    expect($next['value'])->toBe(1500);
    expect($next['direction'])->toBe(1);
});

it('initializes within range when state is null', function () {
    $state = OnlineDriftJob::initState(1500, 1600);
    expect($state['value'])->toBeGreaterThanOrEqual(1500);
    expect($state['value'])->toBeLessThanOrEqual(1600);
    expect($state['direction'])->toBeIn([-1, 1]);
});
