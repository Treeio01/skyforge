<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class BulkController extends Controller
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

    public function activate(Request $request): RedirectResponse
    {
        $count = ($this->model())::whereIn('id', $this->ids($request))->update(['is_active' => true]);

        return back()->with('success', "Активировано: {$count}");
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $count = ($this->model())::whereIn('id', $this->ids($request))->update(['is_active' => false]);

        return back()->with('success', "Деактивировано: {$count}");
    }

    /** @return array<int, int> */
    private function ids(Request $request): array
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        return $data['ids'];
    }
}
