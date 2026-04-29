<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromoCodeBulkController extends Controller
{
    public function activate(Request $request): RedirectResponse
    {
        $ids = $this->ids($request);
        $count = PromoCode::whereIn('id', $ids)->update(['is_active' => true]);

        return back()->with('success', "Активировано: {$count}");
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $ids = $this->ids($request);
        $count = PromoCode::whereIn('id', $ids)->update(['is_active' => false]);

        return back()->with('success', "Деактивировано: {$count}");
    }

    /**
     * @return array<int, int>
     */
    private function ids(Request $request): array
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        return $data['ids'];
    }
}
