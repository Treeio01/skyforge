<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TradeProviderInterface;
use App\DTOs\TradeOfferDTO;
use Illuminate\Support\Str;

class StubTradeProvider implements TradeProviderInterface
{
    public function sendTradeOffer(string $tradeUrl, string $skinMarketHashName): TradeOfferDTO
    {
        return new TradeOfferDTO(
            tradeOfferId: 'stub_'.Str::random(16),
            status: 'sent',
        );
    }

    public function checkTradeStatus(string $tradeOfferId): string
    {
        return 'completed';
    }
}
