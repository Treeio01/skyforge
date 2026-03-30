<?php

declare(strict_types=1);

namespace App\Actions\ProvablyFair;

use App\Models\ProvablyFairSeed;
use App\Models\User;
use Illuminate\Support\Str;

class GenerateSeedPairAction
{
    public function execute(User $user): ProvablyFairSeed
    {
        $serverSeedRaw = Str::random(64);

        return ProvablyFairSeed::create([
            'user_id' => $user->id,
            'client_seed' => Str::random(32),
            'server_seed' => $serverSeedRaw,
            'server_seed_hash' => hash('sha256', $serverSeedRaw),
            'nonce' => 0,
            'is_active' => true,
        ]);
    }
}
