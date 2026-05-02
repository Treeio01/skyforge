<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Skin\IndexSkinsData;
use App\Data\Skin\SearchSkinsData;
use App\Models\Skin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelData\Optional;

class SkinCatalogService
{
    public function listForMarket(IndexSkinsData $data): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        if (! ($data->search instanceof Optional) && $data->search !== null) {
            $query->where('market_hash_name', 'like', '%'.str_replace('%', '', $data->search).'%');
        }

        $this->applyPriceFilters($query, $data->min_price, $data->max_price);
        $this->applySort($query, $data->sort, $data->direction);

        $perPage = $data->per_page instanceof Optional ? 150 : $data->per_page;

        return $query->paginate($perPage)->withQueryString();
    }

    public function listForApi(IndexSkinsData $data): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        if (! ($data->category instanceof Optional)) {
            $query->where('category', $data->category);
        }

        $this->applyPriceFilters($query, $data->min_price, $data->max_price);
        $this->applySort($query, $data->sort, $data->direction);

        $perPage = $data->per_page instanceof Optional ? 50 : $data->per_page;

        return $query->paginate($perPage)->withQueryString();
    }

    public function search(SearchSkinsData $data): ?LengthAwarePaginator
    {
        if ($data->query instanceof Optional || mb_strlen($data->query) < 2) {
            return null;
        }

        $query = $this->baseQuery()
            ->where('market_hash_name', 'like', '%'.str_replace('%', '', $data->query).'%');

        $this->applyPriceFilters($query, $data->min_price, $data->max_price);
        $this->applySort($query, $data->sort, $data->direction);

        $perPage = $data->per_page instanceof Optional ? 50 : $data->per_page;

        return $query->paginate($perPage)->withQueryString();
    }

    private function baseQuery(): Builder
    {
        return Skin::query()->active()->availableForUpgrade();
    }

    private function applyPriceFilters(Builder $query, int|Optional $min, int|Optional $max): void
    {
        if (! ($min instanceof Optional)) {
            $query->where('price', '>=', $min);
        }

        if (! ($max instanceof Optional)) {
            $query->where('price', '<=', $max);
        }
    }

    private function applySort(Builder $query, string|Optional $sort, string|Optional $direction): void
    {
        if ($sort instanceof Optional) {
            $query->orderBy('price');

            return;
        }

        $dir = ($direction instanceof Optional || $direction !== 'desc') ? 'asc' : 'desc';

        match ($sort) {
            'price' => $query->orderBy('price', $dir),
            'name' => $query->orderBy('market_hash_name', $dir),
            default => $query->orderBy('price'),
        };
    }
}
