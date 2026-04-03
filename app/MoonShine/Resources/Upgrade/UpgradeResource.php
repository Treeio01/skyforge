<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Upgrade;

use App\Models\Upgrade;
use App\MoonShine\Resources\Upgrade\Pages\UpgradeDetailPage;
use App\MoonShine\Resources\Upgrade\Pages\UpgradeFormPage;
use App\MoonShine\Resources\Upgrade\Pages\UpgradeIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Upgrade, UpgradeIndexPage, UpgradeFormPage, UpgradeDetailPage>
 */
class UpgradeResource extends ModelResource
{
    protected string $model = Upgrade::class;

    protected string $title = 'Апгрейды';

    protected bool $isCreatable = false;

    protected bool $isEditable = false;

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
}
