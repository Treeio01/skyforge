<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItemMoon;

use App\Models\FaqItem;
use App\MoonShine\Resources\FaqItemMoon\Pages\FaqItemMoonDetailPage;
use App\MoonShine\Resources\FaqItemMoon\Pages\FaqItemMoonFormPage;
use App\MoonShine\Resources\FaqItemMoon\Pages\FaqItemMoonIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;

/**
 * @extends ModelResource<FaqItem, FaqItemMoonIndexPage, FaqItemMoonFormPage, FaqItemMoonDetailPage>
 */
class FaqItemMoonResource extends ModelResource
{
    protected string $model = FaqItem::class;

    protected string $title = 'FAQ Вопросы';

    protected array $with = ['faqCategory'];

    protected string $sortColumn = 'sort_order';

    protected SortDirection $sortDirection = SortDirection::ASC;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            FaqItemMoonIndexPage::class,
            FaqItemMoonFormPage::class,
            FaqItemMoonDetailPage::class,
        ];
    }

    /**
     * @return list<string>
     */
    protected function search(): array
    {
        return ['id', 'category', 'question', 'answer', 'faqCategory.name', 'faqCategory.slug'];
    }

    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->with($this->with);
    }
}
