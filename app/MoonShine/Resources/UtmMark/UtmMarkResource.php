<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UtmMark;

use App\Models\UtmMark;
use App\MoonShine\Resources\UtmMark\Pages\UtmMarkDetailPage;
use App\MoonShine\Resources\UtmMark\Pages\UtmMarkFormPage;
use App\MoonShine\Resources\UtmMark\Pages\UtmMarkIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<UtmMark, UtmMarkIndexPage, UtmMarkFormPage, UtmMarkDetailPage>
 */
class UtmMarkResource extends ModelResource
{
    protected string $model = UtmMark::class;

    protected string $title = 'UTM-метки';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            UtmMarkIndexPage::class,
            UtmMarkFormPage::class,
            UtmMarkDetailPage::class,
        ];
    }

    /**
     * Eager-load aggregate counts so the index page shows them without N+1.
     */
    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->withCount(['users', 'deposits', 'upgrades', 'withdrawals']);
    }
}
