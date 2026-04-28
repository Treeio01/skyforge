<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class UtmMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'is_active',
        'is_admin_created',
        'notes',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_admin_created' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_admin_created' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function upgrades(): HasManyThrough
    {
        return $this->hasManyThrough(Upgrade::class, User::class);
    }

    public function deposits(): HasManyThrough
    {
        return $this->hasManyThrough(Deposit::class, User::class);
    }

    public function withdrawals(): HasManyThrough
    {
        return $this->hasManyThrough(Withdrawal::class, User::class);
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, User::class);
    }

    /**
     * Lookup by slug, or find-or-create by UTM params.
     *
     * @param  array<string, string|null>  $utm
     */
    public static function resolve(?string $refSlug, array $utm): ?self
    {
        if ($refSlug !== null && $refSlug !== '') {
            $bySlug = static::where('slug', $refSlug)->where('is_active', true)->first();

            if ($bySlug) {
                return $bySlug;
            }
        }

        $hasAnyUtm = collect($utm)->filter(fn ($v) => \is_string($v) && $v !== '')->isNotEmpty();

        if (! $hasAnyUtm) {
            return null;
        }

        $payload = [
            'utm_source' => $utm['utm_source'] ?? null,
            'utm_medium' => $utm['utm_medium'] ?? null,
            'utm_campaign' => $utm['utm_campaign'] ?? null,
            'utm_content' => $utm['utm_content'] ?? null,
            'utm_term' => $utm['utm_term'] ?? null,
        ];

        $existing = static::query()
            ->where('utm_source', $payload['utm_source'])
            ->where('utm_medium', $payload['utm_medium'])
            ->where('utm_campaign', $payload['utm_campaign'])
            ->where('utm_content', $payload['utm_content'])
            ->where('utm_term', $payload['utm_term'])
            ->first();

        if ($existing) {
            return $existing;
        }

        return static::create([
            ...$payload,
            'slug' => static::generateAutoSlug($payload),
            'name' => static::buildAutoName($payload),
            'is_active' => true,
            'is_admin_created' => false,
        ]);
    }

    /** @param  array<string, string|null>  $payload */
    private static function generateAutoSlug(array $payload): string
    {
        $base = collect([$payload['utm_source'], $payload['utm_campaign']])
            ->filter()
            ->map(fn ($v) => Str::slug((string) $v, '-'))
            ->implode('-');

        $base = $base !== '' ? $base : 'auto';
        $candidate = mb_substr($base, 0, 48);

        // Уникализируем
        $i = 0;

        while (static::where('slug', $candidate)->exists()) {
            $i++;
            $candidate = mb_substr($base, 0, 48 - \strlen("-{$i}"))."-{$i}";
        }

        return $candidate;
    }

    /** @param  array<string, string|null>  $payload */
    private static function buildAutoName(array $payload): string
    {
        $parts = collect([
            $payload['utm_source'] ? "src:{$payload['utm_source']}" : null,
            $payload['utm_medium'] ? "med:{$payload['utm_medium']}" : null,
            $payload['utm_campaign'] ? "camp:{$payload['utm_campaign']}" : null,
        ])->filter();

        return $parts->isNotEmpty() ? $parts->implode(' / ') : 'Auto UTM';
    }
}
