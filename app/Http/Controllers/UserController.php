<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\UpdateTradeUrlRequest;
use App\Http\Resources\UserProfileResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Profile/Show', [
            'profile' => (new UserProfileResource($user))->resolve($request),
        ]);
    }

    public function updateTradeUrl(UpdateTradeUrlRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('success', 'Trade URL обновлён.');
    }

    public function history(Request $request): Response
    {
        $user = $request->user();
        $type = $request->input('type', 'all');

        $validTypes = array_column(TransactionType::cases(), 'value');

        if ($type !== 'all' && ! in_array($type, $validTypes, true)) {
            $type = 'all';
        }

        $transactions = $user->transactions()
            ->when($type !== 'all', fn ($q) => $q->where('type', $type))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Profile/History', [
            'transactions' => $transactions,
            'filters' => ['type' => $type],
        ]);
    }
}
