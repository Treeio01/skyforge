<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ActivityLog;

use App\MoonShine\Resources\ActivityLog\Pages\ActivityLogDetailPage;
use App\MoonShine\Resources\ActivityLog\Pages\ActivityLogFormPage;
use App\MoonShine\Resources\ActivityLog\Pages\ActivityLogIndexPage;
use App\MoonShine\Resources\Concerns\ReadOnlyActions;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;
use Spatie\Activitylog\Models\Activity;

/**
 * @extends ModelResource<Activity, ActivityLogIndexPage, ActivityLogFormPage, ActivityLogDetailPage>
 */
class ActivityLogResource extends ModelResource
{
    use ReadOnlyActions;

    protected string $model = Activity::class;

    protected string $title = 'Лог активности';

    protected string $sortColumn = 'created_at';

    protected SortDirection $sortDirection = SortDirection::DESC;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ActivityLogIndexPage::class,
            ActivityLogFormPage::class,
            ActivityLogDetailPage::class,
        ];
    }

    /**
     * @return list<string>
     */
    protected function search(): array
    {
        return ['id', 'description', 'log_name', 'subject_type', 'causer_type'];
    }
}
