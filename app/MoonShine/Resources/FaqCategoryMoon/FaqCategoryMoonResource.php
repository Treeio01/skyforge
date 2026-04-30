<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqCategoryMoon;

use App\Models\FaqCategory;
use App\MoonShine\Resources\FaqCategoryMoon\Pages\FaqCategoryMoonDetailPage;
use App\MoonShine\Resources\FaqCategoryMoon\Pages\FaqCategoryMoonFormPage;
use App\MoonShine\Resources\FaqCategoryMoon\Pages\FaqCategoryMoonIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<FaqCategory, FaqCategoryMoonIndexPage, FaqCategoryMoonFormPage, FaqCategoryMoonDetailPage>
 */
class FaqCategoryMoonResource extends ModelResource
{
    protected string $model = FaqCategory::class;

    protected string $title = 'FAQ Категории';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            FaqCategoryMoonIndexPage::class,
            FaqCategoryMoonFormPage::class,
            FaqCategoryMoonDetailPage::class,
        ];
    }

    /**
     * @return list<string>
     */
    protected function search(): array
    {
        return ['id', 'slug', 'name'];
    }
}
