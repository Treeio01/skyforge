<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserSkinStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'steam_id',
        'username',
        'avatar_url',
        'trade_url',
        'last_active_at',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'referrer',
        'registration_ip',
        'utm_mark_id',
    ];

    public function utmMark(): BelongsTo
    {
        return $this->belongsTo(UtmMark::class);
    }

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'total_deposited' => 'integer',
            'total_withdrawn' => 'integer',
            'total_upgraded' => 'integer',
            'total_won' => 'integer',
            'is_banned' => 'boolean',
            'is_admin' => 'boolean',
            'house_edge_override' => 'decimal:2',
            'chance_modifier' => 'decimal:3',
            'last_active_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['is_banned', 'ban_reason', 'is_admin', 'balance', 'house_edge_override', 'chance_modifier'])
            ->logOnlyDirty();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function upgrades(): HasMany
    {
        return $this->hasMany(Upgrade::class);
    }

    public function userSkins(): HasMany
    {
        return $this->hasMany(UserSkin::class);
    }

    public function availableSkins(): HasMany
    {
        return $this->hasMany(UserSkin::class)->where('status', UserSkinStatus::Available);
    }

    public function promoCodeUsages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function activeSeedPair(): HasOne
    {
        return $this->hasOne(ProvablyFairSeed::class)->where('is_active', true);
    }

    public function provablyFairSeeds(): HasMany
    {
        return $this->hasMany(ProvablyFairSeed::class);
    }
}
