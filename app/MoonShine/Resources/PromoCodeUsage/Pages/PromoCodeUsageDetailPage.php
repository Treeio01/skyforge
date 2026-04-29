<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCodeUsage\Pages;

use App\MoonShine\Resources\PromoCodeUsage\PromoCodeUsageResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<PromoCodeUsageResource>
 */
class PromoCodeUsageDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
        ];
    }
}
