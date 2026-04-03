<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\TradeOfferDTO;

interface TradeProviderInterface
{
    public function sendTradeOffer(string $tradeUrl, string $skinMarketHashName): TradeOfferDTO;

    public function checkTradeStatus(string $tradeOfferId): string;
}
