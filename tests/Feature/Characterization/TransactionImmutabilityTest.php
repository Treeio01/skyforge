<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('refuses to update an existing transaction', function () {
    $user = User::factory()->create();
    $tx = Transaction::create([
        'user_id' => $user->id,
        'type' => TransactionType::Deposit->value,
        'amount' => 1_000,
        'balance_before' => 0,
        'balance_after' => 1_000,
        'description' => 'test',
    ]);

    expect(fn () => $tx->update(['amount' => 9_999]))
        ->toThrow(RuntimeException::class, 'immutable');

    $tx->refresh();
    expect($tx->amount)->toBe(1_000);
});

it('refuses to delete an existing transaction', function () {
    $user = User::factory()->create();
    $tx = Transaction::create([
        'user_id' => $user->id,
        'type' => TransactionType::Deposit->value,
        'amount' => 500,
        'balance_before' => 0,
        'balance_after' => 500,
        'description' => 'test',
    ]);

    expect(fn () => $tx->delete())
        ->toThrow(RuntimeException::class, 'immutable');

    expect(Transaction::find($tx->id))->not->toBeNull();
});
