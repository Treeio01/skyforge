<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Withdrawal;

use App\Models\Withdrawal;
use App\MoonShine\Resources\Withdrawal\Pages\WithdrawalDetailPage;
use App\MoonShine\Resources\Withdrawal\Pages\WithdrawalFormPage;
use App\MoonShine\Resources\Withdrawal\Pages\WithdrawalIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Withdrawal, WithdrawalIndexPage, WithdrawalFormPage, WithdrawalDetailPage>
 */
class WithdrawalResource extends ModelResource
{
    protected string $model = Withdrawal::class;

    protected string $title = 'Выводы';

    protected array $with = ['user'];

    protected bool $isCreatable = false;

    protected bool $isEditable = false;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            WithdrawalIndexPage::class,
            WithdrawalFormPage::class,
            WithdrawalDetailPage::class,
        ];
    }

    /**
     * @return list<string>
     */
    protected function search(): array
    {
        return ['id', 'user_id', 'user_skin_id', 'skin_id', 'trade_offer_id', 'trade_offer_status', 'failure_reason', 'user.username', 'user.steam_id', 'skin.market_hash_name'];
    }

    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->with(['user', 'skin', 'userSkin']);
    }
}
