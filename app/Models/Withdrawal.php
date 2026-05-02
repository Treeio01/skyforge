<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawalStatus;
use App\Observers\WithdrawalObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User $user
 * @property-read Skin $skin
 * @property-read UserSkin $userSkin
 */
#[ObservedBy(WithdrawalObserver::class)]
class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_skin_id',
        'skin_id',
        'amount',
        'status',
        'trade_offer_id',
        'trade_offer_status',
        'failure_reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WithdrawalStatus::class,
            'amount' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userSkin(): BelongsTo
    {
        return $this->belongsTo(UserSkin::class);
    }

    public function skin(): BelongsTo
    {
        return $this->belongsTo(Skin::class);
    }
}
