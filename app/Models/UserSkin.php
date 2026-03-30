<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserSkinSource;
use App\Enums\UserSkinStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkin extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price_at_acquisition' => 'integer',
            'source' => UserSkinSource::class,
            'status' => UserSkinStatus::class,
            'withdrawn_at' => 'datetime',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', UserSkinStatus::Available);
    }

    public function scopeInUpgrade(Builder $query): Builder
    {
        return $query->where('status', UserSkinStatus::InUpgrade);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skin(): BelongsTo
    {
        return $this->belongsTo(Skin::class);
    }
}
