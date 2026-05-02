<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Skin\BuySkinsData;
use App\Data\Skin\IndexSkinsData;
use App\Data\Skin\SearchSkinsData;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SkinNotAvailableException;
use App\Http\Resources\SkinBriefResource;
use App\Services\MarketService;
use App\Services\SkinCatalogService;
use App\Support\Admin\MoneyFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class SkinController extends Controller
{
    public function market(IndexSkinsData $data, SkinCatalogService $service): Response
    {
        return Inertia::render('Market/Index', [
            'skins' => SkinBriefResource::collection($service->listForMarket($data)),
        ]);
    }

    public function index(IndexSkinsData $data, SkinCatalogService $service): AnonymousResourceCollection
    {
        return SkinBriefResource::collection($service->listForApi($data));
    }

    public function search(SearchSkinsData $data, SkinCatalogService $service): AnonymousResourceCollection
    {
        $result = $service->search($data);

        return SkinBriefResource::collection($result ?? collect());
    }

    public function buy(BuySkinsData $data, MarketService $service): RedirectResponse
    {
        try {
            $result = $service->buy(request()->user(), $data);
        } catch (SkinNotAvailableException $e) {
            return back()->withErrors(['skin_ids' => $e->getMessage()]);
        } catch (InsufficientBalanceException $e) {
            return back()->with('error', 'Недостаточно средств: '.$e->getMessage());
        }

        return back()->with('success', 'Куплено '.$result['count'].' скинов на сумму '.MoneyFormatter::format($result['total']));
    }
}
