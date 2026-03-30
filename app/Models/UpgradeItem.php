<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpgradeItem extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    public function upgrade(): BelongsTo
    {
        return $this->belongsTo(Upgrade::class);
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
