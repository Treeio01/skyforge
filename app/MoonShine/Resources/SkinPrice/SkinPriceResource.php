<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\SkinPrice;

use App\Models\SkinPrice;
use App\MoonShine\Resources\SkinPrice\Pages\SkinPriceDetailPage;
use App\MoonShine\Resources\SkinPrice\Pages\SkinPriceFormPage;
use App\MoonShine\Resources\SkinPrice\Pages\SkinPriceIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<SkinPrice, SkinPriceIndexPage, SkinPriceFormPage, SkinPriceDetailPage>
 */
class SkinPriceResource extends ModelResource
{
    protected string $model = SkinPrice::class;

    protected string $title = 'Цены скинов';

    protected array $search = ['skin_id'];

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            SkinPriceIndexPage::class,
            SkinPriceFormPage::class,
            SkinPriceDetailPage::class,
        ];
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE, Action::UPDATE, Action::DELETE, Action::MASS_DELETE);
    }
}
