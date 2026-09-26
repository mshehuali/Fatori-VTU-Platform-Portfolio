<?php

namespace App\Jobs;

use App\Models\PaymentRequest;
use App\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 30;

    public function __construct(
        protected string $gateway,
        protected array $payload,
    ) {
    }

    /**
     * Process a normalized payment webhook asynchronously.
     */
    public function handle(WalletService $walletService): void
    {
        try {
            $reference = $this->payload['reference'] ?? null;

            $amount = isset($this->payload['amount'])
                ? round((float) $this->payload['amount'], 2)
                : null;

            $status = $this->payload['status'] ?? null;

            $gatewayReference = $this->payload['gateway_reference'] ?? null;

            if (! $reference || $amount === null || ! $status) {
                return;
            }

            // Only successful payment events can credit a wallet.
            if (! in_array($status, ['success', 'successful', 'completed'], true)) {
                return;
            }

            DB::transaction(function () use (
                $walletService,
                $reference,
                $amount,
                $gatewayReference,
            ) {
                $payment = PaymentRequest::where('reference', $reference)
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    Log::warning('Webhook payment request not found.', [
                        'reference' => $reference,
                    ]);

                    return;
                }

                // Idempotency guard.
                if ($payment->status === 'processed') {
                    return;
                }

                // Verify the gateway reference when both sides provide one.
                if (
                    $gatewayReference !== null &&
                    $payment->gateway_id !== null &&
                    $payment->gateway_id !== $gatewayReference
                ) {
                    $payment->update([
                        'status' => 'gateway_mismatch',
                        'gateway_response' => $this->payload,
                    ]);

                    return;
                }

                // Never trust the webhook amount blindly.
                if (round((float) $payment->amount, 2) !== $amount) {
                    $payment->update([
                        'status' => 'amount_mismatch',
                        'gateway_response' => $this->payload,
                    ]);

                    return;
                }

                $walletService->credit(
                    $payment->user,
                    $payment->amount,
                    $this->gateway,
                    'Payment webhook wallet funding',
                    [
                        'gateway_reference' => $gatewayReference,
                        'webhook' => $this->payload,
                    ],
                    $payment->reference,
                );

                $payment->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'gateway_response' => $this->payload,
                ]);
            });
        } catch (Throwable $e) {
            Log::error('Payment webhook job failed.', [
                'gateway' => $this->gateway,
                'error' => $e->getMessage(),
            ]);

            // Allow the queue worker to retry the job.
            throw $e;
        }
    }
}