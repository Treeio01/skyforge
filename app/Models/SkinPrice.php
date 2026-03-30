<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkinPrice extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'fetched_at' => 'datetime',
        ];
    }

    public function skin(): BelongsTo
    {
        return $this->belongsTo(Skin::class);
    }
}
