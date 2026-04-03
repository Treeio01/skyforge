<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\Deposit\DepositResource;
use App\MoonShine\Resources\PromoCode\PromoCodeResource;
use App\MoonShine\Resources\Setting\SettingResource;
use App\MoonShine\Resources\Skin\SkinResource;
use App\MoonShine\Resources\Transaction\TransactionResource;
use App\MoonShine\Resources\Upgrade\UpgradeResource;
use App\MoonShine\Resources\User\UserResource;
use App\MoonShine\Resources\Withdrawal\WithdrawalResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     */
    public function boot(CoreContract $core, MoonShineConfigurator $config): void
    {
        // $config->authEnable();

        $core
            ->resources([
                // Resources will be registered here,
                UserResource::class,
                SkinResource::class,
                TransactionResource::class,
                DepositResource::class,
                WithdrawalResource::class,
                UpgradeResource::class,
                SettingResource::class,
                PromoCodeResource::class,
            ])
            ->pages([
                // Custom pages will be registered here
            ]);
    }
}
