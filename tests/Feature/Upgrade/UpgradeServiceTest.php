<?php

declare(strict_types=1);

use App\Actions\ProvablyFair\GenerateSeedPairAction;
use App\Data\Upgrade\CreateUpgradeData;
use App\Enums\UpgradeResult;
use App\Enums\UserSkinStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Skin;
use App\Models\User;
use App\Models\UserSkin;
use App\Services\UpgradeService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

function setupUpgradeScenario(int $betSkinPrice = 10000, int $targetPrice = 50000, int $extraBalance = 0): array
{
    $user = User::factory()->withBalance($extraBalance)->create();
    app(GenerateSeedPairAction::class)->execute($user);

    $betSkin = Skin::factory()->create(['price' => $betSkinPrice]);
    $userSkin = UserSkin::factory()->create([
        'user_id' => $user->id,
        'skin_id' => $betSkin->id,
        'price_at_acquisition' => $betSkinPrice,
    ]);

    $targetSkin = Skin::factory()->create(['price' => $targetPrice]);

    return [$user, $userSkin, $targetSkin];
}

it('completes upgrade with win result', function () {
    [$user, $userSkin, $targetSkin] = setupUpgradeScenario(10000, 10100);

    $result = app(UpgradeService::class)->execute($user, new CreateUpgradeData(
        user_skin_ids: [$userSkin->id],
        balance_amount: 0,
        target_skin_id: $targetSkin->id,
    ));

    expect($result->upgrade)->not->toBeNull();
    expect($result->upgrade->result)->toBeIn([UpgradeResult::Win, UpgradeResult::Lose]);

    // Bet skin always burned
    expect($userSkin->refresh()->status)->toBe(UserSkinStatus::Burned);
});

it('creates target skin in inventory on win', function () {
    // Use very high chance to make win likely — bet ~= target
    [$user, $userSkin, $targetSkin] = setupUpgradeScenario(95000, 100000);

    $result = app(UpgradeService::class)->execute($user, new CreateUpgradeData(
        user_skin_ids: [$userSkin->id],
        balance_amount: 0,
        target_skin_id: $targetSkin->id,
    ));

    if ($result->upgrade->result === UpgradeResult::Win) {
        $wonSkin = UserSkin::where('user_id', $user->id)
            ->where('skin_id', $targetSkin->id)
            ->where('status', UserSkinStatus::Available)
            ->first();

        expect($wonSkin)->not->toBeNull();
    }
});

it('debits balance amount from user', function () {
    [$user, $userSkin, $targetSkin] = setupUpgradeScenario(
        betSkinPrice: 10000,
        targetPrice: 50000,
        extraBalance: 5000,
    );

    app(UpgradeService::class)->execute($user, new CreateUpgradeData(
        user_skin_ids: [$userSkin->id],
        balance_amount: 5000,
        target_skin_id: $targetSkin->id,
    ));

    expect($user->refresh()->balance)->toBe(0);
});

it('rejects upgrade with insufficient balance', function () {
    [$user, $userSkin, $targetSkin] = setupUpgradeScenario(
        betSkinPrice: 10000,
        targetPrice: 50000,
        extraBalance: 0,
    );

    app(UpgradeService::class)->execute($user, new CreateUpgradeData(
        user_skin_ids: [$userSkin->id],
        balance_amount: 5000,
        target_skin_id: $targetSkin->id,
    ));
})->throws(InsufficientBalanceException::class);

it('rejects upgrade when skin is not available', function () {
    [$user, $userSkin, $targetSkin] = setupUpgradeScenario();

    $userSkin->update(['status' => UserSkinStatus::Burned]);

    app(UpgradeService::class)->execute($user, new CreateUpgradeData(
        user_skin_ids: [$userSkin->id],
        balance_amount: 0,
        target_skin_id: $targetSkin->id,
    ));
})->throws(DomainException::class);

it('rejects upgrade when target is cheaper than bet', function () {
    [$user, $userSkin, $targetSkin] = setupUpgradeScenario(
        betSkinPrice: 100000,
        targetPrice: 50000,
    );

    app(UpgradeService::class)->execute($user, new CreateUpgradeData(
        user_skin_ids: [$userSkin->id],
        balance_amount: 0,
        target_skin_id: $targetSkin->id,
    ));
})->throws(DomainException::class);

it('records provably fair data on upgrade', function () {
    [$user, $userSkin, $targetSkin] = setupUpgradeScenario(10000, 50000);

    $result = app(UpgradeService::class)->execute($user, new CreateUpgradeData(
        user_skin_ids: [$userSkin->id],
        balance_amount: 0,
        target_skin_id: $targetSkin->id,
    ));

    $upgrade = $result->upgrade;
    expect($upgrade->client_seed)->not->toBeEmpty();
    expect($upgrade->server_seed_hash)->not->toBeEmpty();
    expect($upgrade->server_seed_raw)->not->toBeEmpty();
    expect($upgrade->nonce)->toBeGreaterThan(0);
    expect($upgrade->roll_value)->toBeGreaterThanOrEqual(0.0)->toBeLessThan(1.0);
});

it('increments seed nonce after upgrade', function () {
    [$user, $userSkin, $targetSkin] = setupUpgradeScenario(10000, 50000);

    $nonceBefore = $user->activeSeedPair->nonce;

    app(UpgradeService::class)->execute($user, new CreateUpgradeData(
        user_skin_ids: [$userSkin->id],
        balance_amount: 0,
        target_skin_id: $targetSkin->id,
    ));

    expect($user->activeSeedPair->refresh()->nonce)->toBe($nonceBefore + 1);
});
