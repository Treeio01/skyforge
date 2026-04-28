<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\OnlineUpdated;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OnlineDriftJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        if (! Setting::get('online.enabled', false)) {
            return;
        }

        $tickDefault = 8;
        $lock = Cache::lock('online.loop', $tickDefault * 2);

        if (! $lock->get()) {
            return;
        }

        try {
            $min = (int) Setting::get('online.min', 1500);
            $max = (int) Setting::get('online.max', 1600);
            $tick = (int) Setting::get('online.tick_seconds', 8);
            $maxStep = (int) Setting::get('online.max_step', 3);

            if ($min >= $max) {
                Log::warning('online drift: min >= max, skipping', compact('min', 'max'));

                return;
            }

            Cache::put('online.loop_heartbeat', now()->timestamp, $tick * 3);

            $state = Cache::get('online.fake_state') ?? self::initState($min, $max);
            $state = self::computeNext($state, $min, $max, $maxStep);

            Cache::put('online.fake_state', $state, $tick * 5);

            event(new OnlineUpdated($state['value']));
        } finally {
            $lock->release();
        }

        self::dispatch()->onQueue('online')->delay(now()->addSeconds($tick));
    }

    /**
     * Pure: next drift step within [min, max], with occasional direction flip.
     *
     * @param  array{value:int,direction:int}  $state
     * @return array{value:int,direction:int}
     */
    public static function computeNext(array $state, int $min, int $max, int $maxStep): array
    {
        $direction = $state['direction'];

        $step = random_int(1, $maxStep) * $direction;
        $value = max($min, min($max, $state['value'] + $step));

        // Boundary flips take precedence
        if ($value === $min) {
            $direction = 1;
        } elseif ($value === $max) {
            $direction = -1;
        } else {
            // 15% случайных разворотов для естественного движения (away from bounds)
            if (random_int(0, 99) < 15) {
                $direction = -$direction;
            }
        }

        return ['value' => $value, 'direction' => $direction];
    }

    /** @return array{value:int,direction:int} */
    public static function initState(int $min, int $max): array
    {
        return [
            'value' => random_int($min, $max),
            'direction' => random_int(0, 1) === 0 ? -1 : 1,
        ];
    }
}
