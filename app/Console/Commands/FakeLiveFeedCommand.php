<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Skin;
use App\Services\UpgradeStatsService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Console\Command;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Redis;

class FakeLiveFeedCommand extends Command
{
    protected $signature = 'feed:fake {--interval=7 : Average interval in seconds}';

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

    public function handle(UpgradeStatsService $upgradeStats): int
    {
        $interval = max(1, (int) $this->option('interval'));

        $this->info("Generating fake feed every ~{$interval}s. Press Ctrl+C to stop.");

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

        $id = 100000;

        while (true) {
            $skin = $skins->random();
            $isWin = rand(1, 100) <= 35;
            $chance = round(rand(50, 9000) / 100, 2);

            $payload = [
                'id' => $id++,
                'username' => self::FAKE_NAMES[array_rand(self::FAKE_NAMES)],
                'avatar_url' => self::FAKE_AVATARS[array_rand(self::FAKE_AVATARS)],
                'target_skin_name' => $skin->market_hash_name,
                'target_skin_image' => $skin->image_path
                    ? asset('storage/'.$skin->image_path)
                    : null,
                'rarity_color' => $skin->rarity_color,
                'chance' => $chance,
                'result' => $isWin ? 'win' : 'lose',
                'created_at' => now()->toISOString(),
            ];

            // Сохраняем в Redis для /api/live-feed
            Redis::lpush('feed:recent', json_encode($payload));
            Redis::ltrim('feed:recent', 0, 29);
            $upgradeStats->incrementFakeAndBroadcast();

            broadcast(new class($payload) implements ShouldBroadcastNow
            {
                use Dispatchable;

                public function __construct(public array $payload) {}

                public function broadcastOn(): array
                {
                    return [new Channel('upgrades')];
                }

                public function broadcastAs(): string
                {
                    return 'UpgradeCompleted';
                }

                public function broadcastWith(): array
                {
                    return $this->payload;
                }
            });

            $delay = rand(max(1, $interval - 3), $interval + 3);
            sleep($delay);
        }
    }
}
