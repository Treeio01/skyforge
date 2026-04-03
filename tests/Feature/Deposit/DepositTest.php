<?php

declare(strict_types=1);

use App\Actions\Deposit\CompleteDepositAction;
use App\Enums\DepositMethod;
use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('creates deposit via controller', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('deposit.store'), [
            'amount' => 10000,
            'method' => 'sbp',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Deposit::where('user_id', $user->id)->count())->toBe(1);

    $deposit = Deposit::first();
    expect($deposit)
        ->method->toBe(DepositMethod::Sbp)
        ->amount->toBe(10000)
        ->status->toBe(DepositStatus::Pending);
});

it('completes deposit and credits balance', function () {
    $user = User::factory()->create();
    $deposit = Deposit::factory()->create([
        'user_id' => $user->id,
        'amount' => 25000,
        'status' => DepositStatus::Pending,
    ]);

    $result = app(CompleteDepositAction::class)->execute($deposit);

    expect($result)->toBeTrue();
    expect($deposit->refresh()->status)->toBe(DepositStatus::Completed);
    expect($user->refresh()->balance)->toBe(25000);
});

it('idempotency: does not double-credit on repeat complete', function () {
    $user = User::factory()->create();
    $deposit = Deposit::factory()->create([
        'user_id' => $user->id,
        'amount' => 10000,
        'status' => DepositStatus::Pending,
    ]);

    $action = app(CompleteDepositAction::class);

    $first = $action->execute($deposit);
    $second = $action->execute($deposit->refresh());

    expect($first)->toBeTrue();
    expect($second)->toBeFalse();
    expect($user->refresh()->balance)->toBe(10000);
});

it('rejects 6th pending deposit', function () {
    $user = User::factory()->create();
    Deposit::factory()->count(5)->create([
        'user_id' => $user->id,
        'status' => DepositStatus::Pending,
    ]);

    $this->actingAs($user)
        ->post(route('deposit.store'), [
            'amount' => 10000,
            'method' => 'sbp',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Deposit::where('user_id', $user->id)->count())->toBe(5);
});

it('webhook completes deposit', function () {
    $user = User::factory()->create();
    $deposit = Deposit::factory()->create([
        'user_id' => $user->id,
        'amount' => 30000,
        'provider_id' => 'test_provider_123',
    ]);

    $this->postJson(route('webhook.payment'), [
        'provider_id' => 'test_provider_123',
        'amount' => 30000,
    ])->assertSuccessful();

    expect($deposit->refresh()->status)->toBe(DepositStatus::Completed);
    expect($user->refresh()->balance)->toBe(30000);
});

it('webhook returns 404 for unknown deposit', function () {
    $this->postJson(route('webhook.payment'), [
        'provider_id' => 'nonexistent',
        'amount' => 1000,
    ])->assertNotFound();
});
