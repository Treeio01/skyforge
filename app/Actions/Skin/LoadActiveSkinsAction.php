<?php

declare(strict_types=1);

namespace App\Actions\Skin;

use App\Models\Skin;
use Illuminate\Database\Eloquent\Collection;

class LoadActiveSkinsAction
{
    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, Skin>
     */
    public function execute(array $ids): Collection
    {
        return Skin::query()->whereIn('id', $ids)->where('is_active', true)->get();
    }
}
