<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OnlineDriftJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        // populated in next tasks
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
