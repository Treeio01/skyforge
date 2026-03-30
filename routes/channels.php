<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Public channels: anyone can listen.
| Private channels: only authenticated + authorized users.
|
*/

// Private: user's personal channel (balance updates, deposit/withdrawal status)
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public: live upgrade feed (all users see all upgrades)
// No auth needed — defined as public channel on frontend via Echo.channel()
