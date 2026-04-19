<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SkinCategory;
use App\Enums\SkinExterior;
use App\Enums\SkinRarity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skin extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'exterior' => SkinExterior::class,
            'rarity' => SkinRarity::class,
            'category' => SkinCategory::class,
            'price' => 'integer',
            'is_active' => 'boolean',
            'is_available_for_upgrade' => 'boolean',
            'price_updated_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableForUpgrade(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_available_for_upgrade', true)
            ->where('price', '>', 0)
            ->where('price', '<', 100_000_000)
            ->whereIn('category', ['weapon', 'knife', 'gloves']);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(SkinPrice::class);
    }

    public function userSkins(): HasMany
    {
        return $this->hasMany(UserSkin::class);
    }
}
