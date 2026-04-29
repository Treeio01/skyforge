<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_enabled' => ['required', 'boolean'],
            'withdrawals_enabled' => ['required', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:2000'],

            'house_edge' => ['required', 'numeric', 'min:0', 'max:50'],
            'min_upgrade_chance' => ['required', 'numeric', 'min:0.1', 'max:99'],
            'max_upgrade_chance' => ['required', 'numeric', 'min:1', 'max:99', 'gte:min_upgrade_chance'],
            'min_bet_amount' => ['required', 'integer', 'min:1'],
            'max_bet_amount' => ['required', 'integer', 'gt:min_bet_amount'],
            'upgrade_cooldown' => ['required', 'integer', 'min:0', 'max:300'],

            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'favicon_url' => ['nullable', 'string', 'max:500'],

            'social_vk' => ['nullable', 'url', 'max:500'],
            'social_telegram' => ['nullable', 'url', 'max:500'],
            'social_discord' => ['nullable', 'url', 'max:500'],
            'social_tiktok' => ['nullable', 'url', 'max:500'],
            'social_youtube' => ['nullable', 'url', 'max:500'],
            'social_twitch' => ['nullable', 'url', 'max:500'],
        ]);

        $bools = ['site_enabled', 'withdrawals_enabled'];
        $floats = ['house_edge', 'min_upgrade_chance', 'max_upgrade_chance'];
        $integers = ['min_bet_amount', 'max_bet_amount', 'upgrade_cooldown'];

        foreach ($data as $key => $value) {
            $type = match (true) {
                \in_array($key, $bools, true) => 'boolean',
                \in_array($key, $floats, true) => 'float',
                \in_array($key, $integers, true) => 'integer',
                default => 'string',
            };

            Setting::set($key, $value ?? '', $type);
        }

        return back()->with('success', 'Настройки сайта обновлены');
    }
}
