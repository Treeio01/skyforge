<?php

declare(strict_types=1);

namespace App\Actions\ProvablyFair;

use App\Models\ProvablyFairSeed;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RotateClientSeedAction
{
    public function __construct(
        private GenerateSeedPairAction $generateSeedPair,
    ) {}

    /** @return array{revealed: ?string, new: ProvablyFairSeed} */
    public function execute(User $user, string $clientSeed): array
    {
        return DB::transaction(function () use ($user, $clientSeed) {
            $oldSeed = $user->activeSeedPair;
            $oldSeed?->update(['is_active' => false]);

            $newSeed = $this->generateSeedPair->execute($user);
            $newSeed->update(['client_seed' => $clientSeed]);

            return [
                'revealed' => $oldSeed?->server_seed,
                'new' => $newSeed,
            ];
        });
    }
}
