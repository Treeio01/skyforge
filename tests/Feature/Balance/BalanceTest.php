<?php

declare(strict_types=1);

use App\Actions\Balance\CreditBalanceAction;
use App\Actions\Balance\DebitBalanceAction;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('credits balance and creates transaction', function () {
    $user = User::factory()->create();

    $tx = app(CreditBalanceAction::class)->execute(
        $user,
        10000,
        TransactionType::Deposit,
    );

    expect($user->refresh()->balance)->toBe(10000);
    expect($tx)
        ->type->toBe(TransactionType::Deposit)
        ->amount->toBe(10000)
        ->balance_before->toBe(0)
        ->balance_after->toBe(10000);
});

it('credits updates total_deposited for deposit type', function () {
    $user = User::factory()->create();

    app(CreditBalanceAction::class)->execute($user, 5000, TransactionType::Deposit);

    expect($user->refresh()->total_deposited)->toBe(5000);
});

it('debits balance and creates transaction', function () {
    $user = User::factory()->withBalance(50000)->create();

    $tx = app(DebitBalanceAction::class)->execute(
        $user,
        20000,
        TransactionType::UpgradeBet,
    );

    expect($user->refresh()->balance)->toBe(30000);
    expect($tx)
        ->amount->toBe(-20000)
        ->balance_before->toBe(50000)
        ->balance_after->toBe(30000);
});

it('throws InsufficientBalanceException on overdraft', function () {
    $user = User::factory()->withBalance(1000)->create();

    app(DebitBalanceAction::class)->execute($user, 5000, TransactionType::UpgradeBet);
})->throws(InsufficientBalanceException::class);

it('handles concurrent credit safely with locking', function () {
    $user = User::factory()->create();

    $credit = app(CreditBalanceAction::class);

    $credit->execute($user, 10000, TransactionType::Deposit);
    $credit->execute($user, 20000, TransactionType::Deposit);

    expect($user->refresh()->balance)->toBe(30000);
    expect(Transaction::where('user_id', $user->id)->count())->toBe(2);
});
