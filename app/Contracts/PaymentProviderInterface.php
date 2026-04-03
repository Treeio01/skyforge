<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\PaymentDTO;
use App\DTOs\WebhookDTO;
use Illuminate\Http\Request;

interface PaymentProviderInterface
{
    /** @param array<string, mixed> $meta */
    public function createPayment(int $amount, string $method, array $meta = []): PaymentDTO;

    public function verifyWebhook(Request $request): WebhookDTO;

    public function getPaymentStatus(string $providerId): string;
}
