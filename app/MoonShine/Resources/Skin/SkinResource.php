<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Skin;

use App\Models\Skin;
use App\MoonShine\Resources\Skin\Pages\SkinDetailPage;
use App\MoonShine\Resources\Skin\Pages\SkinFormPage;
use App\MoonShine\Resources\Skin\Pages\SkinIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;

/**
 * @extends ModelResource<Skin, SkinIndexPage, SkinFormPage, SkinDetailPage>
 */
class SkinResource extends ModelResource
{
    protected string $model = Skin::class;

    protected string $title = 'Скины';

    protected array $search = ['market_hash_name', 'weapon_type', 'skin_name'];

    protected string $sortColumn = 'price';

    protected SortDirection $sortDirection = SortDirection::DESC;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            SkinIndexPage::class,
            SkinFormPage::class,
            SkinDetailPage::class,
        ];
    }
}
