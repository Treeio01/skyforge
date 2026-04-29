<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UserSkin;

use App\Models\UserSkin;
use App\MoonShine\Resources\UserSkin\Pages\UserSkinDetailPage;
use App\MoonShine\Resources\UserSkin\Pages\UserSkinFormPage;
use App\MoonShine\Resources\UserSkin\Pages\UserSkinIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<UserSkin, UserSkinIndexPage, UserSkinFormPage, UserSkinDetailPage>
 */
class UserSkinResource extends ModelResource
{
    protected string $model = UserSkin::class;

    protected string $title = 'Инвентарь пользователей';

    protected array $search = ['id', 'user_id'];

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            UserSkinIndexPage::class,
            UserSkinFormPage::class,
            UserSkinDetailPage::class,
        ];
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE, Action::UPDATE, Action::DELETE, Action::MASS_DELETE);
    }

    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->with(['user', 'skin']);
    }
}
