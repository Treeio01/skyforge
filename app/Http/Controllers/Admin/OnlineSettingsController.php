<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OnlineSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'online_enabled' => ['required', 'boolean'],
            'online_min' => ['required', 'integer', 'min:0'],
            'online_max' => ['required', 'integer', 'min:1', 'gt:online_min'],
            'online_tick_seconds' => ['required', 'integer', 'min:3', 'max:60'],
            'online_max_step' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $wasEnabled = (bool) Setting::get('online.enabled', false);

        Setting::set('online.enabled', (bool) $data['online_enabled'], 'boolean');
        Setting::set('online.min', (int) $data['online_min'], 'integer');
        Setting::set('online.max', (int) $data['online_max'], 'integer');
        Setting::set('online.tick_seconds', (int) $data['online_tick_seconds'], 'integer');
        Setting::set('online.max_step', (int) $data['online_max_step'], 'integer');

        Cache::forget('online.fake_state');

        if (! $wasEnabled && (bool) $data['online_enabled']) {
            OnlineDriftJob::dispatch()->onQueue('online');
        }

        return back()->with('success', 'Настройки применены, обновляются на всех клиентах');
    }

    public function reset(): RedirectResponse
    {
        Cache::forget('online.fake_state');

        return back()->with('success', 'Текущее значение сброшено');
    }
}
