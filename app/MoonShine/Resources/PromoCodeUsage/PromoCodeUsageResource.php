<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCodeUsage;

use App\Models\PromoCodeUsage;
use App\MoonShine\Resources\Concerns\ReadOnlyActions;
use App\MoonShine\Resources\PromoCodeUsage\Pages\PromoCodeUsageDetailPage;
use App\MoonShine\Resources\PromoCodeUsage\Pages\PromoCodeUsageFormPage;
use App\MoonShine\Resources\PromoCodeUsage\Pages\PromoCodeUsageIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<PromoCodeUsage, PromoCodeUsageIndexPage, PromoCodeUsageFormPage, PromoCodeUsageDetailPage>
 */
class PromoCodeUsageResource extends ModelResource
{
    use ReadOnlyActions;

    protected string $model = PromoCodeUsage::class;

    protected string $title = 'Использования промокодов';

    protected array $search = ['id', 'user_id', 'promo_code_id'];

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            PromoCodeUsageIndexPage::class,
            PromoCodeUsageFormPage::class,
            PromoCodeUsageDetailPage::class,
        ];
    }

    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->with(['user', 'promoCode']);
    }
}
