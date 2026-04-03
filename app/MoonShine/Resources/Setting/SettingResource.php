<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Setting;

use App\Models\Setting;
use App\MoonShine\Resources\Setting\Pages\SettingDetailPage;
use App\MoonShine\Resources\Setting\Pages\SettingFormPage;
use App\MoonShine\Resources\Setting\Pages\SettingIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Setting, SettingIndexPage, SettingFormPage, SettingDetailPage>
 */
class SettingResource extends ModelResource
{
    protected string $model = Setting::class;

    protected string $title = 'Настройки';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            SettingIndexPage::class,
            SettingFormPage::class,
            SettingDetailPage::class,
        ];
    }
}
