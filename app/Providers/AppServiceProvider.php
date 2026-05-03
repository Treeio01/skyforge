<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Contracts\TradeProviderInterface;
use App\Events\UpgradeCompleted;
use App\Listeners\PushToLiveFeed;
use App\Models\Setting;
use App\Services\AuthBridge\ConsumerDomainRegistry;
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

        $this->app->singleton(ConsumerDomainRegistry::class, fn () => ConsumerDomainRegistry::fromConfig());
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
            $cooldownSeconds = (int) Setting::get('upgrade_cooldown', 2);
            $key = $request->user()?->id ?: $request->ip();

            if ($cooldownSeconds <= 0) {
                return Limit::none()->by($key);
            }

            return Limit::perSecond(1, $cooldownSeconds)->by($key);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('promo', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return [
                Limit::perMinute(5)->by($key),
                Limit::perHour(30)->by($key),
            ];
        });

        RateLimiter::for('deposit', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('withdraw', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('tradeUrl', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('sellSkins', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('seed', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('feed', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
