<?php

declare(strict_types=1);

use App\Actions\Deposit\CompleteDepositAction;
use App\Enums\DepositMethod;
use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('credits balance only once even if webhook fires twice', function () {
    $user = User::factory()->withBalance(0)->create();
    $deposit = Deposit::create([
        'user_id' => $user->id,
        'amount' => 5_000,
        'method' => DepositMethod::Sbp->value,
        'status' => DepositStatus::Pending->value,
        'provider_id' => 'stub_test_'.uniqid(),
        'idempotency_key' => 'test_'.uniqid(),
    ]);

    $action = app(CompleteDepositAction::class);

    $first = $action->execute($deposit);
    $second = $action->execute($deposit->fresh());

    expect($first)->toBeTrue();
    expect($second)->toBeFalse();
    expect($user->fresh()->balance)->toBe(5_000);
});
