<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Http\Request;

class PullAttributionAction
{
    /** @return array<string, mixed> */
    public function execute(): array
    {
        $request = app(Request::class);

        if (! $request->hasSession()) {
            return [];
        }

        $data = $request->session()->pull('attribution', []);

        return is_array($data) ? $data : [];
    }
}
