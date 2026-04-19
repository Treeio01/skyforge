<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItemMoon;

use App\Models\FaqItem;
use App\MoonShine\Resources\FaqItemMoon\Pages\FaqItemMoonDetailPage;
use App\MoonShine\Resources\FaqItemMoon\Pages\FaqItemMoonFormPage;
use App\MoonShine\Resources\FaqItemMoon\Pages\FaqItemMoonIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<FaqItem, FaqItemMoonIndexPage, FaqItemMoonFormPage, FaqItemMoonDetailPage>
 */
class FaqItemMoonResource extends ModelResource
{
    protected string $model = FaqItem::class;

    protected string $title = 'FAQ Вопросы';

    protected array $with = ['faqCategory'];

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
}
