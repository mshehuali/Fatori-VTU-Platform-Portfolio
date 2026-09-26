<?php

namespace App\Contracts;

use App\Models\PaymentRequest;

interface PaymentGatewayInterface
{
    /**
     * Initialize a payment without contacting a real external provider.
     *
     * @return array{
     *     status: string,
     *     gateway_reference: string,
     *     metadata: array
     * }
     */
    public function initialize(PaymentRequest $payment): array;

    /**
     * Verify a webhook signature for the configured demo gateway.
     */
    public function verifyWebhookSignature(string $payload, ?string $signature): bool;

    /**
     * Normalize an incoming webhook payload.
     *
     * @return array{
     *     reference: string|null,
     *     amount: float|null,
     *     status: string|null,
     *     gateway_reference: string|null
     * }
     */
    public function normalizeWebhook(array $payload): array;
}
