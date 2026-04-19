<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Skin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Skin */
class SkinBriefResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'market_hash_name' => $this->market_hash_name,
            'image_url' => $this->image_path ? asset('storage/'.$this->image_path) : null,
            'price' => $this->price,
            'rarity_color' => $this->rarity_color,
            'exterior' => $this->exterior?->value,
            'category' => $this->category?->value,
        ];
    }
}
