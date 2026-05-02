<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Models\UtmMark;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;

class UserObserver
{
    public function creating(User $user): void
    {
        if ($user->registration_ip === null) {
            $user->registration_ip = Request::ip();
        }

        if ($user->referrer === null) {
            $user->referrer = (string) Request::header('referer', '');
        }

        $this->captureUtm($user);
    }

    public function updated(User $user): void
    {
        if (! $user->wasChanged('is_banned')) {
            return;
        }

        if ((bool) $user->is_banned === true) {
            $user->forceFill(['last_active_at' => null])->saveQuietly();
        }
    }

    private function captureUtm(User $user): void
    {
        $sources = [
            'utm_source' => 'utm_source',
            'utm_medium' => 'utm_medium',
            'utm_campaign' => 'utm_campaign',
            'utm_content' => 'utm_content',
            'utm_term' => 'utm_term',
        ];

        foreach ($sources as $field => $key) {
            if ($user->{$field} !== null) {
                continue;
            }

            $value = Cookie::get($key) ?? Request::query($key);

            if (is_string($value) && $value !== '') {
                $user->{$field} = $value;
            }
        }

        if ($user->utm_mark_id === null) {
            $slug = Cookie::get('ref') ?? Request::query('ref');

            if (is_string($slug) && $slug !== '') {
                $mark = UtmMark::query()->where('slug', $slug)->where('is_active', true)->first();

                if ($mark !== null) {
                    $user->utm_mark_id = $mark->id;
                }
            }
        }
    }
}
