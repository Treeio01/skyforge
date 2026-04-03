<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Upgrade;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class UpgradeCompleted implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public Upgrade $upgrade,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel('upgrades')];
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->upgrade->id,
            'username' => $this->upgrade->user->username,
            'avatar_url' => $this->upgrade->user->avatar_url,
            'target_skin_name' => $this->upgrade->targetSkin->market_hash_name,
            'target_skin_image' => $this->upgrade->targetSkin->image_path
                ? asset('storage/'.$this->upgrade->targetSkin->image_path)
                : null,
            'chance' => $this->upgrade->chance,
            'result' => $this->upgrade->result->value,
            'created_at' => $this->upgrade->created_at->toISOString(),
        ];
    }
}
