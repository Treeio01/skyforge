<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User;

use App\Models\User;
use App\MoonShine\Resources\User\Pages\UserDetailPage;
use App\MoonShine\Resources\User\Pages\UserFormPage;
use App\MoonShine\Resources\User\Pages\UserIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<User, UserIndexPage, UserFormPage, UserDetailPage>
 */
class UserResource extends ModelResource
{
    protected string $model = User::class;

    protected string $title = 'Пользователи';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            UserIndexPage::class,
            UserFormPage::class,
            UserDetailPage::class,
        ];
    }
}
