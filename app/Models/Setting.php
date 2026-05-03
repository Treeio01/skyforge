<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    private const FRONTEND_BUNDLE_CACHE_KEY = 'settings.bundle.frontend';

    /**
     * Keys read together in HandleInertiaRequests (single cache entry / one DB round-trip).
     *
     * @var list<string>
     */
    public const FRONTEND_BUNDLE_KEYS = [
        'online.enabled',
        'social_vk',
        'social_telegram',
        'social_discord',
        'social_tiktok',
        'social_youtube',
        'social_twitch',
        'house_edge',
        'min_upgrade_chance',
        'max_upgrade_chance',
        'min_bet_amount',
        'max_bet_amount',
        'upgrade_cooldown',
    ];

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value'])
            ->logOnlyDirty();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember("settings.{$key}", 60, function () use ($key) {
            $setting = static::where('key', $key)->first();

            if (! $setting) {
                return null;
            }

            return self::castValue($setting->value, $setting->type);
        });

        return $value ?? $default;
    }

    /**
     * Cached snapshot of public UI settings touched on every Inertia request.
     *
     * @return array<string, mixed>
     */
    public static function frontendBundle(): array
    {
        return Cache::remember(self::FRONTEND_BUNDLE_CACHE_KEY, 60, function (): array {
            $keys = self::FRONTEND_BUNDLE_KEYS;

            /** @var Collection<string, Setting> $rows */
            $rows = static::query()->whereIn('key', $keys)->get()->keyBy('key');

            $out = [];

            foreach ($keys as $key) {
                $row = $rows->get($key);
                $out[$key] = $row instanceof self ? self::castValue($row->value, $row->type) : null;
            }

            return $out;
        });
    }

    public static function set(string $key, mixed $value, ?string $type = null): void
    {
        $attrs = ['value' => static::stringify($value), 'updated_at' => now()];

        if ($type !== null) {
            $attrs['type'] = $type;
        }

        static::updateOrCreate(['key' => $key], $attrs);

        Cache::forget("settings.{$key}");

        if (in_array($key, self::FRONTEND_BUNDLE_KEYS, true)) {
            Cache::forget(self::FRONTEND_BUNDLE_CACHE_KEY);
        }
    }

    private static function stringify(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_array($value) => json_encode($value),
            default => (string) $value,
        };
    }

    private static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
