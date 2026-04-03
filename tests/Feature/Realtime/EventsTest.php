<?php

declare(strict_types=1);

use App\Actions\Balance\CreditBalanceAction;
use App\Actions\Deposit\CompleteDepositAction;
use App\Actions\ProvablyFair\GenerateSeedPairAction;
use App\Enums\DepositStatus;
use App\Enums\TransactionType;
use App\Events\BalanceUpdated;
use App\Events\DepositCompleted;
use App\Events\UpgradeCompleted;
use App\Models\Deposit;
use App\Models\Skin;
use App\Models\User;
use App\Models\UserSkin;
use App\Services\UpgradeService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(LazilyRefreshDatabase::class);

it('dispatches BalanceUpdated on credit', function () {
    Event::fake([BalanceUpdated::class]);

    $user = User::factory()->create();
    app(CreditBalanceAction::class)->execute($user, 10000, TransactionType::Deposit);

    Event::assertDispatched(BalanceUpdated::class, fn ($e) => $e->user->id === $user->id);
});

it('dispatches DepositCompleted on deposit completion', function () {
    Event::fake([DepositCompleted::class, BalanceUpdated::class]);

    $user = User::factory()->create();
    $deposit = Deposit::factory()->create([
        'user_id' => $user->id,
        'amount' => 5000,
        'status' => DepositStatus::Pending,
    ]);

    app(CompleteDepositAction::class)->execute($deposit);

    Event::assertDispatched(DepositCompleted::class, fn ($e) => $e->deposit->id === $deposit->id);
});

it('dispatches UpgradeCompleted on upgrade', function () {
    Event::fake([UpgradeCompleted::class, BalanceUpdated::class]);

    $user = User::factory()->create();
    app(GenerateSeedPairAction::class)->execute($user);

    $betSkin = Skin::factory()->create(['price' => 10000]);
    $userSkin = UserSkin::factory()->create([
        'user_id' => $user->id,
        'skin_id' => $betSkin->id,
        'price_at_acquisition' => 10000,
    ]);
    $target = Skin::factory()->create(['price' => 50000]);

    app(UpgradeService::class)->execute(
        user: $user,
        userSkinIds: [$userSkin->id],
        balanceAmount: 0,
        targetSkinId: $target->id,
    );

    Event::assertDispatched(UpgradeCompleted::class);
});

it('returns live feed from api endpoint', function () {
    $this->getJson(route('live-feed'))
        ->assertSuccessful()
        ->assertJsonStructure(['data']);
});
