<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | House Edge
    |--------------------------------------------------------------------------
    |
    | Default house edge percentage for upgrade calculations.
    | Can be overridden via the admin panel (settings table).
    |
    */
    'house_edge' => (float) env('SKYFORGE_HOUSE_EDGE', 5.00),

    /*
    |--------------------------------------------------------------------------
    | Upgrade Limits
    |--------------------------------------------------------------------------
    */
    'min_bet_amount' => (int) env('SKYFORGE_MIN_BET', 100),          // 1 RUB in kopecks
    'max_bet_amount' => (int) env('SKYFORGE_MAX_BET', 100_000_000),  // 1M RUB in kopecks
    'min_upgrade_chance' => (float) env('SKYFORGE_MIN_CHANCE', 1.00),
    'max_upgrade_chance' => (float) env('SKYFORGE_MAX_CHANCE', 95.00),
    'upgrade_cooldown' => (int) env('SKYFORGE_UPGRADE_COOLDOWN', 2), // seconds

    /*
    |--------------------------------------------------------------------------
    | Price Sync
    |--------------------------------------------------------------------------
    */
    'price_sync' => [
        'enabled' => (bool) env('SKYFORGE_PRICE_SYNC_ENABLED', true),
        'interval' => (int) env('SKYFORGE_PRICE_SYNC_INTERVAL', 15), // minutes
        'source_url' => env('SKYFORGE_PRICE_SOURCE_URL', 'https://market.csgo.com/api/v2/prices/RUB.json'),
        'change_threshold' => (float) env('SKYFORGE_PRICE_CHANGE_THRESHOLD', 5.0), // % change to log to history
    ],

    /*
    |--------------------------------------------------------------------------
    | Skins Storage
    |--------------------------------------------------------------------------
    */
    'skins' => [
        'disk' => env('SKYFORGE_SKINS_DISK', 'public'),
        'path' => env('SKYFORGE_SKINS_PATH', 'skins'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Steam
    |--------------------------------------------------------------------------
    */
    'steam' => [
        'api_key' => env('STEAM_API_KEY'),
        'callback_url' => env('STEAM_CALLBACK_URL', '/auth/steam/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Provider
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'provider' => env('SKYFORGE_PAYMENT_PROVIDER', 'stub'),
        'api_key' => env('SKYFORGE_PAYMENT_API_KEY'),
        'api_secret' => env('SKYFORGE_PAYMENT_API_SECRET'),
        'webhook_secret' => env('SKYFORGE_PAYMENT_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'description' => env('SKYFORGE_SEO_DESCRIPTION', 'SKYFORGE — апгрейд скинов CS2. Улучшайте свои предметы в пару кликов с системой Provably Fair.'),
        'keywords' => env('SKYFORGE_SEO_KEYWORDS', 'CS2 скины, апгрейд скинов, CS2 upgrade, CSGO скины, Provably Fair, рулетка скинов'),
        'og_title' => env('SKYFORGE_SEO_OG_TITLE', 'Апгрейд скинов CS2'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Links
    |--------------------------------------------------------------------------
    */
    'socials' => [
        'vk' => env('SKYFORGE_SOCIAL_VK', 'https://vk.com/skyforge'),
        'telegram' => env('SKYFORGE_SOCIAL_TELEGRAM', 'https://t.me/skyforge'),
        'discord' => env('SKYFORGE_SOCIAL_DISCORD', 'https://discord.gg/skyforge'),
        'tiktok' => env('SKYFORGE_SOCIAL_TIKTOK', 'https://tiktok.com/@skyforge'),
        'youtube' => env('SKYFORGE_SOCIAL_YOUTUBE', 'https://youtube.com/@skyforge'),
        'twitch' => env('SKYFORGE_SOCIAL_TWITCH', 'https://twitch.tv/skyforge'),
    ],

];
