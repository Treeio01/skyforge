<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UpgradeResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upgrade extends Model
{
    public $timestamps = false;

    protected $hidden = [
        'server_seed_raw',
    ];

    protected $fillable = [
        'user_id',
        'target_skin_id',
        'bet_amount',
        'balance_amount',
        'target_price',
        'chance',
        'multiplier',
        'house_edge',
        'chance_modifier',
        'result',
        'roll_value',
        'roll_hex',
        'client_seed',
        'server_seed_hash',
        'server_seed_raw',
        'nonce',
        'is_revealed',
    ];

    protected function casts(): array
    {
        return [
            'bet_amount' => 'integer',
            'balance_amount' => 'integer',
            'target_price' => 'integer',
            'chance' => 'decimal:5',
            'multiplier' => 'decimal:2',
            'house_edge' => 'decimal:2',
            'chance_modifier' => 'decimal:3',
            'result' => UpgradeResult::class,
            'roll_value' => 'double',
            'nonce' => 'integer',
            'is_revealed' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetSkin(): BelongsTo
    {
        return $this->belongsTo(Skin::class, 'target_skin_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(UpgradeItem::class);
    }
}
