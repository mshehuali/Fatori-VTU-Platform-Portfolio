<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentRequest;

class DemoPaymentGateway implements PaymentGatewayInterface
{
    public function initialize(PaymentRequest $payment): array
    {
        return [
            'status' => 'initiated',
            'gateway_reference' => 'DEMO-' . strtoupper(bin2hex(random_bytes(6))),
            'metadata' => [
                'gateway' => 'demo',
                'mode' => 'sandbox',
            ],
        ];
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $secret = (string) config('services.demo_payment.webhook_secret');

        if ($secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    public function normalizeWebhook(array $payload): array
    {
        return [
            'reference' => data_get($payload, 'reference'),
            'amount' => is_numeric(data_get($payload, 'amount'))
                ? (float) data_get($payload, 'amount')
                : null,
            'status' => data_get($payload, 'status'),
            'gateway_reference' => data_get($payload, 'gateway_reference'),
        ];
    }
}
