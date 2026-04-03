<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepositMethod;
use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property DepositStatus $status
 */
class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method',
        'amount',
        'status',
        'provider_id',
        'idempotency_key',
        'provider_data',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'method' => DepositMethod::class,
            'status' => DepositStatus::class,
            'amount' => 'integer',
            'provider_data' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
