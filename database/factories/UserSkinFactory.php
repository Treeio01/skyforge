<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserSkinSource;
use App\Enums\UserSkinStatus;
use App\Models\Skin;
use App\Models\User;
use App\Models\UserSkin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSkin>
 */
class UserSkinFactory extends Factory
{
    protected $model = UserSkin::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'skin_id' => Skin::factory(),
            'price_at_acquisition' => fake()->numberBetween(100, 5_000_000),
            'source' => UserSkinSource::Deposit,
            'status' => UserSkinStatus::Available,
        ];
    }

    public function fromUpgrade(): static
    {
        return $this->state(['source' => UserSkinSource::UpgradeWin]);
    }

    public function burned(): static
    {
        return $this->state(['status' => UserSkinStatus::Burned]);
    }

    public function withdrawn(): static
    {
        return $this->state([
            'status' => UserSkinStatus::Withdrawn,
            'withdrawn_at' => now(),
        ]);
    }
}
