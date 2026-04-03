<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentProviderInterface;
use App\DTOs\PaymentDTO;
use App\DTOs\WebhookDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StubPaymentProvider implements PaymentProviderInterface
{
    /** @param array<string, mixed> $meta */
    public function createPayment(int $amount, string $method, array $meta = []): PaymentDTO
    {
        return new PaymentDTO(
            amount: $amount,
            method: $method,
            providerId: 'stub_'.Str::random(16),
            redirectUrl: null,
        );
    }

    public function verifyWebhook(Request $request): WebhookDTO
    {
        return new WebhookDTO(
            providerId: $request->input('provider_id', ''),
            status: 'completed',
            amount: (int) $request->input('amount', 0),
            signatureValid: true,
            rawData: $request->all(),
        );
    }

    public function getPaymentStatus(string $providerId): string
    {
        return 'completed';
    }
}
