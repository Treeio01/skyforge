<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Contracts\TradeProviderInterface;
use App\Events\UpgradeCompleted;
use App\Listeners\PushToLiveFeed;
use App\Services\StubPaymentProvider;
use App\Services\StubTradeProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Steam\SteamExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentProviderInterface::class, match (config('skyforge.payment.provider')) {
            default => StubPaymentProvider::class,
        });

        $this->app->bind(TradeProviderInterface::class, StubTradeProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Vite::prefetch(concurrency: 3);

        Event::listen(SocialiteWasCalled::class, SteamExtendSocialite::class);
        Event::listen(UpgradeCompleted::class, PushToLiveFeed::class);

        RateLimiter::for('upgrade', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
