<?php

declare(strict_types=1);

use App\Enums\UserSkinStatus;
use App\Enums\WithdrawalStatus;
use App\Jobs\ProcessWithdrawalJob;
use App\Models\Skin;
use App\Models\User;
use App\Models\UserSkin;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(LazilyRefreshDatabase::class);

it('creates withdrawal and marks skin as withdrawn', function () {
    Queue::fake();

    $user = User::factory()->create(['trade_url' => 'https://steamcommunity.com/tradeoffer/new/?partner=123&token=abc']);
    $skin = Skin::factory()->create();
    $userSkin = UserSkin::factory()->create([
        'user_id' => $user->id,
        'skin_id' => $skin->id,
        'price_at_acquisition' => $skin->price,
    ]);

    $this->actingAs($user)
        ->post(route('withdrawal.store'), ['user_skin_id' => $userSkin->id])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($userSkin->refresh()->status)->toBe(UserSkinStatus::Withdrawn);
    expect(Withdrawal::where('user_id', $user->id)->count())->toBe(1);

    Queue::assertPushed(ProcessWithdrawalJob::class);
});

it('rejects withdrawal without trade url', function () {
    $user = User::factory()->create(['trade_url' => null]);

    $this->actingAs($user)
        ->post(route('withdrawal.store'), ['user_skin_id' => 1])
        ->assertRedirect(route('profile'));
});

it('rejects 4th pending withdrawal', function () {
    Queue::fake();

    $user = User::factory()->create(['trade_url' => 'https://steamcommunity.com/tradeoffer/new/?partner=123&token=abc']);

    for ($i = 0; $i < 3; $i++) {
        $skin = Skin::factory()->create();
        $us = UserSkin::factory()->create([
            'user_id' => $user->id,
            'skin_id' => $skin->id,
            'price_at_acquisition' => $skin->price,
        ]);
        Withdrawal::factory()->create([
            'user_id' => $user->id,
            'user_skin_id' => $us->id,
            'skin_id' => $skin->id,
            'amount' => $us->price_at_acquisition,
            'status' => WithdrawalStatus::Pending,
        ]);
    }

    $newSkin = Skin::factory()->create();
    $newUserSkin = UserSkin::factory()->create([
        'user_id' => $user->id,
        'skin_id' => $newSkin->id,
        'price_at_acquisition' => $newSkin->price,
    ]);

    $this->actingAs($user)
        ->post(route('withdrawal.store'), ['user_skin_id' => $newUserSkin->id])
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('returns skin to inventory on job failure', function () {
    $user = User::factory()->create(['trade_url' => 'https://steamcommunity.com/tradeoffer/new/?partner=123&token=abc']);
    $skin = Skin::factory()->create();
    $userSkin = UserSkin::factory()->create([
        'user_id' => $user->id,
        'skin_id' => $skin->id,
        'price_at_acquisition' => $skin->price,
        'status' => UserSkinStatus::Withdrawn,
    ]);
    $withdrawal = Withdrawal::factory()->create([
        'user_id' => $user->id,
        'user_skin_id' => $userSkin->id,
        'skin_id' => $skin->id,
        'amount' => $userSkin->price_at_acquisition,
        'status' => WithdrawalStatus::Processing,
    ]);

    $job = new ProcessWithdrawalJob($withdrawal);
    $job->failed(new RuntimeException('Steam API error'));

    expect($withdrawal->refresh()->status)->toBe(WithdrawalStatus::Failed);
    expect($userSkin->refresh()->status)->toBe(UserSkinStatus::Available);
});
