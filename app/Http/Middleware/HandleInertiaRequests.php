<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use App\Models\Setting;
use App\Models\User;
use App\Services\UpgradeStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // Обновляем last_active_at раз в минуту
        if ($user && (! $user->last_active_at || $user->last_active_at->diffInMinutes(now()) >= 1)) {
            $user->update(['last_active_at' => now()]);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? (new UserResource($user))->resolve($request) : null,
            ],
            'flash' => [
                'error' => $request->session()->get('error'),
                'success' => $request->session()->get('success'),
            ],
            'stats' => Cache::remember('site_stats', 30, fn () => [
                'online_real' => User::where('last_active_at', '>=', now()->subMinutes(5))->count(),
                'online_fake_initial' => Cache::get('online.fake_state')['value'] ?? 0,
                'online_enabled' => (bool) Setting::get('online.enabled', false),
                'total_upgrades' => app(UpgradeStatsService::class)->total(),
            ]),
            'socials' => [
                'vk' => Setting::get('social_vk', ''),
                'telegram' => Setting::get('social_telegram', ''),
                'discord' => Setting::get('social_discord', ''),
                'tiktok' => Setting::get('social_tiktok', ''),
                'youtube' => Setting::get('social_youtube', ''),
                'twitch' => Setting::get('social_twitch', ''),
            ],
        ];
    }
}
