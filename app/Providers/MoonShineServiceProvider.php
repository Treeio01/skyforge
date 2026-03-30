<?php

declare(strict_types=1);

namespace App\Providers;

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
                // Resources will be registered here
            ])
            ->pages([
                // Custom pages will be registered here
            ]);
    }
}
