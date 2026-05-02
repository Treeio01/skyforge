<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class IssueSessionAction
{
    public function execute(User $user, bool $remember = true): void
    {
        Auth::login($user, $remember);
    }
}
