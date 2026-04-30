<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UpgradeResult;
use App\Events\UpgradeCompleted;
use App\Models\Skin;
use App\Models\Upgrade;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FakeLiveFeedCommand extends Command
{
    protected $signature = 'feed:fake
        {--interval=7 : Average interval in seconds}
        {--once : Generate a finite batch and exit}
        {--count=6 : Number of entries to generate in --once mode}';

    protected $description = 'Generate fake live feed entries and broadcast them via WebSocket';

    private const FAKE_NAMES = [
        'xDarkLord', 'SniperElite', 'CyberNinja', 'AceHunter', 'ShadowFox',
        'BlazeMaster', 'GhostRider', 'StormBreaker', 'IronWolf', 'NightHawk',
        'PixelKing', 'VortexPro', 'ZeroGravity', 'ThunderBolt', 'CryptoKid',
        'NeonSamurai', 'StarDust', 'PhantomX', 'WildCard', 'RocketMan',
        'DiamondHands', 'MoonWalker', 'FireStorm', 'IceBreaker', 'SkyFall',
    ];

    private const FAKE_AVATARS = [
        'https://avatars.steamstatic.com/b5bd56c1aa4644a474a2e4a190e80b357e1e5e6b_medium.jpg',
        'https://avatars.steamstatic.com/fef49e7fa7e1997310d705b2a6158ff8dc1cdfeb_medium.jpg',
        'https://avatars.steamstatic.com/1c526efa6c47a043ed96c0e7a3e8b4dce53437f3_medium.jpg',
    ];

    public function handle(): int
    {
        $interval = max(1, (int) $this->option('interval'));
        $once = (bool) $this->option('once');
        $count = max(1, (int) $this->option('count'));

        $skins = Skin::query()
            ->where('is_active', true)
            ->whereIn('category', ['weapon', 'knife', 'gloves'])
            ->where('price', '>', 10000)
            ->inRandomOrder()
            ->limit(200)
            ->get();

        if ($skins->isEmpty()) {
            $this->error('No active skins found.');

            return self::FAILURE;
        }

        if ($once) {
            for ($i = 0; $i < $count; $i++) {
                $this->createFakeUpgrade($skins);
            }

            $this->info("Generated {$count} fake feed upgrades.");

            return self::SUCCESS;
        }

        $this->info("Generating fake feed every ~{$interval}s. Press Ctrl+C to stop.");

        while (true) {
            $this->createFakeUpgrade($skins);

            $delay = rand(max(1, $interval - 3), $interval + 3);
            sleep($delay);
        }
    }

    /**
     * @param  Collection<int, Skin>  $skins
     */
    private function createFakeUpgrade(Collection $skins): void
    {
        $skin = $skins->random();
        $isWin = rand(1, 100) <= 35;
        $chance = round(rand(50, 9000) / 100, 2);
        $targetPrice = (int) $skin->price;
        $betAmount = max(100, (int) round($targetPrice * ($chance / 100) / 0.95));
        $betAmount = min($betAmount, max(100, $targetPrice - 1));
        $user = $this->fakeUser();

        $upgrade = Upgrade::create([
            'user_id' => $user->id,
            'target_skin_id' => $skin->id,
            'bet_amount' => $betAmount,
            'balance_amount' => 0,
            'target_price' => $targetPrice,
            'chance' => $chance,
            'multiplier' => max(1.01, round($targetPrice / max(1, $betAmount), 2)),
            'house_edge' => 5.00,
            'chance_modifier' => 1.000,
            'result' => $isWin ? UpgradeResult::Win : UpgradeResult::Lose,
            'roll_value' => rand(0, 10_000_000) / 10_000_000,
            'roll_hex' => bin2hex(random_bytes(8)),
            'client_seed' => hash('sha256', 'fake-client-'.uniqid('', true)),
            'server_seed_hash' => hash('sha256', 'fake-server-'.uniqid('', true)),
            'server_seed_raw' => hash('sha256', 'fake-raw-'.uniqid('', true)),
            'nonce' => random_int(1, 1_000_000),
            'is_revealed' => true,
            'is_fake' => true,
            'created_at' => now(),
        ]);

        $upgrade->load(['user', 'targetSkin']);
        UpgradeCompleted::dispatch($upgrade);
    }

    private function fakeUser(): User
    {
        $name = self::FAKE_NAMES[array_rand(self::FAKE_NAMES)];
        $steamId = '9'.str_pad((string) abs(crc32($name)), 19, '0', STR_PAD_LEFT);

        return User::query()->updateOrCreate(
            ['steam_id' => $steamId],
            [
                'username' => $name,
                'avatar_url' => self::FAKE_AVATARS[array_rand(self::FAKE_AVATARS)],
                'last_active_at' => now(),
            ],
        );
    }
}
