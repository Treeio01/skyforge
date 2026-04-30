<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Upgrade;

use App\Models\Upgrade;
use App\MoonShine\Resources\Upgrade\Pages\UpgradeDetailPage;
use App\MoonShine\Resources\Upgrade\Pages\UpgradeFormPage;
use App\MoonShine\Resources\Upgrade\Pages\UpgradeIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;

/**
 * @extends ModelResource<Upgrade, UpgradeIndexPage, UpgradeFormPage, UpgradeDetailPage>
 */
class UpgradeResource extends ModelResource
{
    protected string $model = Upgrade::class;

    protected string $title = 'Апгрейды';

    protected bool $isCreatable = false;

    protected bool $isEditable = false;

    protected string $sortColumn = 'id';

    protected SortDirection $sortDirection = SortDirection::DESC;

    protected array $with = ['user', 'targetSkin'];

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            UpgradeIndexPage::class,
            UpgradeFormPage::class,
            UpgradeDetailPage::class,
        ];
    }

    /**
     * @return list<string>
     */
    protected function search(): array
    {
        return ['id', 'user_id', 'target_skin_id', 'result', 'user.username', 'user.steam_id', 'targetSkin.market_hash_name'];
    }

    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->with($this->with);
    }
}
