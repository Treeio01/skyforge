<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Skin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Skin */
class SkinResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'market_hash_name' => $this->market_hash_name,
            'weapon_type' => $this->weapon_type,
            'skin_name' => $this->skin_name,
            'exterior' => $this->exterior,
            'rarity' => $this->rarity,
            'rarity_color' => $this->rarity_color,
            'category' => $this->category,
            'image_url' => $this->image_path ? asset('storage/'.$this->image_path) : null,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'is_available_for_upgrade' => $this->is_available_for_upgrade,
        ];
    }
}
