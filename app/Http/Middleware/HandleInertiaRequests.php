<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use App\Models\Setting;
use App\Models\User;
use App\Services\UpgradeStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        if ($user && (! $user->last_active_at || $user->last_active_at->diffInMinutes(now()) >= 1)) {
            $userId = $user->getKey();

            app()->terminating(function () use ($userId): void {
                $fresh = User::query()->find($userId);

                if ($fresh === null) {
                    return;
                }

                $last = $fresh->last_active_at;

                if (! $last || $last->diffInMinutes(now()) >= 1) {
                    $fresh->update(['last_active_at' => now()]);
                }
            });
        }

        $frontend = Setting::frontendBundle();

        $inertiaFlash = Inertia::getFlashed($request);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? (new UserResource($user))->resolve($request) : null,
                'loginUrl' => $this->buildLoginUrl($request),
            ],
            'flash' => [
                'error' => $inertiaFlash['error'] ?? $request->session()->get('error'),
                'success' => $inertiaFlash['success'] ?? $request->session()->get('success'),
                'upgrade_roll' => $inertiaFlash['upgrade_roll'] ?? null,
            ],
            'stats' => Cache::remember('site_stats', 30, fn () => [
                'online_real' => User::where('last_active_at', '>=', now()->subMinutes(5))->count(),
                'online_fake_initial' => Cache::get('online.fake_state')['value'] ?? 0,
                'online_enabled' => (bool) ($frontend['online.enabled'] ?? false),
                'total_upgrades' => app(UpgradeStatsService::class)->total(),
            ]),
            'socials' => [
                'vk' => (string) ($frontend['social_vk'] ?? ''),
                'telegram' => (string) ($frontend['social_telegram'] ?? ''),
                'discord' => (string) ($frontend['social_discord'] ?? ''),
                'tiktok' => (string) ($frontend['social_tiktok'] ?? ''),
                'youtube' => (string) ($frontend['social_youtube'] ?? ''),
                'twitch' => (string) ($frontend['social_twitch'] ?? ''),
            ],
            'upgradeSettings' => [
                'houseEdge' => (float) ($frontend['house_edge'] ?? 5.00),
                'minChance' => (float) ($frontend['min_upgrade_chance'] ?? 1.00),
                'maxChance' => (float) ($frontend['max_upgrade_chance'] ?? 95.00),
                'minBetAmount' => (int) ($frontend['min_bet_amount'] ?? 100),
                'maxBetAmount' => (int) ($frontend['max_bet_amount'] ?? 5_000_000),
                'cooldownSeconds' => (int) ($frontend['upgrade_cooldown'] ?? 2),
            ],
        ];
    }

    /**
     * When the auth bridge is enabled, the login button points at the auth
     * domain with the current page URL as the return target. Otherwise the
     * legacy direct Steam route is used (for local dev / fallback).
     */
    private function buildLoginUrl(Request $request): string
    {
        if (! (bool) config('auth_bridge.enabled')) {
            return route('auth.steam');
        }

        $authDomain = (string) config('auth_bridge.auth_domain');

        return 'https://'.$authDomain.'/login?return='.urlencode($request->fullUrl());
    }
}
