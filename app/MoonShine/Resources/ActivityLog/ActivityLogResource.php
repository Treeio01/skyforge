<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ActivityLog;

use App\MoonShine\Resources\ActivityLog\Pages\ActivityLogDetailPage;
use App\MoonShine\Resources\ActivityLog\Pages\ActivityLogFormPage;
use App\MoonShine\Resources\ActivityLog\Pages\ActivityLogIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use Spatie\Activitylog\Models\Activity;

/**
 * @extends ModelResource<Activity, ActivityLogIndexPage, ActivityLogFormPage, ActivityLogDetailPage>
 */
class ActivityLogResource extends ModelResource
{
    protected string $model = Activity::class;

    protected string $title = 'Лог активности';

    protected string $sortColumn = 'created_at';

    protected string $sortDirection = 'DESC';

    protected array $search = ['description', 'log_name', 'subject_type', 'causer_type'];

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

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE, Action::UPDATE, Action::DELETE, Action::MASS_DELETE);
    }
}
