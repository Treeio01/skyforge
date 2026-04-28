<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

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

    public static function set(string $key, mixed $value, ?string $type = null): void
    {
        $attrs = ['value' => static::stringify($value), 'updated_at' => now()];

        if ($type !== null) {
            $attrs['type'] = $type;
        }

        static::updateOrCreate(['key' => $key], $attrs);

        Cache::forget("settings.{$key}");
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
