<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItem;

use App\Models\FaqItem;
use App\MoonShine\Resources\FaqItem\Pages\FaqItemFormPage;
use App\MoonShine\Resources\FaqItem\Pages\FaqItemIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;

/**
 * @extends ModelResource<FaqItem>
 */
class FaqItemResource extends ModelResource
{
    protected string $model = FaqItem::class;

    protected string $title = 'FAQ';

    protected string $sortColumn = 'sort_order';

    protected SortDirection $sortDirection = SortDirection::ASC;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            FaqItemIndexPage::class,
            FaqItemFormPage::class,
        ];
    }
}
