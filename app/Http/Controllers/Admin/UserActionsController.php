<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserActionsController extends Controller
{
    public function ban(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'is_banned' => true,
            'ban_reason' => $data['reason'] ?? null,
        ]);

        return back()->with('success', "{$user->username} забанен.");
    }

    public function unban(User $user): RedirectResponse
    {
        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
        ]);

        return back()->with('success', "{$user->username} разбанен.");
    }
}
