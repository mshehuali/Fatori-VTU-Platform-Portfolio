<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Jobs\ProcessPaymentWebhook;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewayInterface $gateway,
    ) {
    }

    /**
     * Receive a provider-neutral payment webhook.
     *
     * The HTTP layer verifies authenticity, validates the payload
     * structure, records the event once, and queues processing.
     *
     * Wallet balances are never modified directly by the controller.
     */
    public function handle(Request $request)
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('X-Demo-Payment-Signature');

        if (! $this->gateway->verifyWebhookSignature($rawPayload, $signature)) {
            return response()->json([
                'message' => 'Invalid webhook signature.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            return response()->json([
                'message' => 'Invalid webhook payload.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $normalized = $this->gateway->normalizeWebhook($payload);

        if (
            empty($normalized['reference']) ||
            $normalized['amount'] === null ||
            empty($normalized['status'])
        ) {
            return response()->json([
                'message' => 'Webhook payload is missing required fields.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $event = WebhookEvent::firstOrCreate(
            [
                'reference' => $normalized['reference'],
            ],
            [
                'gateway' => 'demo',
                'payload' => $payload,
            ],
        );

        if ($event->wasRecentlyCreated) {
            ProcessPaymentWebhook::dispatch(
                'demo',
                $normalized,
            );
        }

        return response()->json([
            'status' => 'accepted',
            'duplicate' => ! $event->wasRecentlyCreated,
        ]);
    }
}