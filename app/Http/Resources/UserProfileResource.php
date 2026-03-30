<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...(new UserResource($this->resource))->toArray($request),
            'total_deposited' => $this->total_deposited,
            'total_withdrawn' => $this->total_withdrawn,
            'total_upgraded' => $this->total_upgraded,
            'total_won' => $this->total_won,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
