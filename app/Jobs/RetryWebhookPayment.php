<?php

namespace App\Jobs;

use App\Models\PaymentRequest;
use App\Models\WebhookFailure;
use App\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RetryWebhookPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 30;

    public function __construct(
        public string $reference
    ) {
    }

    public function handle(WalletService $walletService): void
    {
        try {
            DB::transaction(function () use ($walletService) {
                $failure = WebhookFailure::where(
                    'reference',
                    $this->reference
                )
                    ->lockForUpdate()
                    ->first();

                $payment = PaymentRequest::where(
                    'reference',
                    $this->reference
                )
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    if ($failure) {
                        $failure->delete();
                    }

                    return;
                }

                if ($payment->status === 'processed') {
                    if ($failure) {
                        $failure->delete();
                    }

                    return;
                }

                if (! $failure) {
                    return;
                }

                $walletService->credit(
                    $payment->user,
                    $payment->amount,
                    'webhook_retry',
                    'Recovered payment webhook',
                    [
                        'retry' => true,
                        'attempt' => $failure->retries + 1,
                        'source' => 'webhook_retry',
                    ],
                    $payment->reference,
                );

                $payment->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                ]);

                $failure->delete();
            });
        } catch (Throwable $e) {
            $failure = WebhookFailure::where(
                'reference',
                $this->reference
            )->first();

            if ($failure) {
                $failure->increment('retries');

                Log::error('Webhook retry failed.', [
                    'reference' => $this->reference,
                    'retries' => $failure->retries,
                    'error' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }
}