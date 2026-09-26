<?php

use App\Jobs\ProcessPaymentWebhook;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('accepts a valid signed payment webhook and queues processing', function () {
    Queue::fake();

    $user = User::factory()->create();

    $payment = PaymentRequest::create([
        'reference' => 'PAY-TEST-001',
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
        'gateway_id' => 'DEMO-TEST-001',
    ]);

    $payload = [
        'reference' => $payment->reference,
        'amount' => 1000,
        'status' => 'success',
        'gateway_reference' => $payment->gateway_id,
    ];

    $rawPayload = json_encode($payload);

    $signature = hash_hmac(
        'sha256',
        $rawPayload,
        config('services.demo_payment.webhook_secret')
    );

    $response = $this->postJson(
        '/api/payment/webhook',
        $payload,
        ['X-Demo-Payment-Signature' => $signature]
    );

    $response
        ->assertOk()
        ->assertJson([
            'status' => 'accepted',
            'duplicate' => false,
        ]);

    $this->assertDatabaseHas('webhook_events', [
        'reference' => $payment->reference,
        'gateway' => 'demo',
    ]);

    Queue::assertPushed(ProcessPaymentWebhook::class);
});

it('rejects a webhook with an invalid signature', function () {
    $payload = [
        'reference' => 'PAY-INVALID-SIGNATURE',
        'amount' => 1000,
        'status' => 'success',
        'gateway_reference' => 'DEMO-INVALID',
    ];

    $response = $this->postJson(
        '/api/payment/webhook',
        $payload,
        ['X-Demo-Payment-Signature' => 'invalid-signature']
    );

    $response
        ->assertUnauthorized()
        ->assertJson([
            'message' => 'Invalid webhook signature.',
        ]);

    $this->assertDatabaseCount('webhook_events', 0);
});

it('rejects a webhook with missing required fields', function () {
    $payload = [
        'reference' => 'PAY-INCOMPLETE',
        'amount' => 1000,
    ];

    $rawPayload = json_encode($payload);

    $signature = hash_hmac(
        'sha256',
        $rawPayload,
        config('services.demo_payment.webhook_secret')
    );

    $response = $this->postJson(
        '/api/payment/webhook',
        $payload,
        ['X-Demo-Payment-Signature' => $signature]
    );

    $response
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'Webhook payload is missing required fields.',
        ]);

    $this->assertDatabaseCount('webhook_events', 0);
});

it('does not queue the same webhook twice', function () {
    Queue::fake();

    $user = User::factory()->create();

    $payment = PaymentRequest::create([
        'reference' => 'PAY-DUPLICATE-001',
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
        'gateway_id' => 'DEMO-DUPLICATE-001',
    ]);

    $payload = [
        'reference' => $payment->reference,
        'amount' => 1000,
        'status' => 'success',
        'gateway_reference' => $payment->gateway_id,
    ];

    $rawPayload = json_encode($payload);

    $signature = hash_hmac(
        'sha256',
        $rawPayload,
        config('services.demo_payment.webhook_secret')
    );

    $headers = [
        'X-Demo-Payment-Signature' => $signature,
    ];

    $first = $this->postJson(
        '/api/payment/webhook',
        $payload,
        $headers
    );

    $second = $this->postJson(
        '/api/payment/webhook',
        $payload,
        $headers
    );

    $first
        ->assertOk()
        ->assertJson([
            'status' => 'accepted',
            'duplicate' => false,
        ]);

    $second
        ->assertOk()
        ->assertJson([
            'status' => 'accepted',
            'duplicate' => true,
        ]);

    $this->assertDatabaseCount('webhook_events', 1);

    Queue::assertPushed(ProcessPaymentWebhook::class, 1);
});

it('processes a successful webhook and credits the wallet exactly once', function () {
    $user = User::factory()->create();

    $walletService = app(\App\Services\WalletService::class);

    $walletService->getOrCreateWalletFor($user);

    $payment = PaymentRequest::create([
        'reference' => 'PAY-PROCESS-001',
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
        'gateway_id' => 'DEMO-PROCESS-001',
    ]);

    $payload = [
        'reference' => $payment->reference,
        'amount' => 1000,
        'status' => 'success',
        'gateway_reference' => $payment->gateway_id,
    ];

    ProcessPaymentWebhook::dispatchSync('demo', $payload);

    $payment->refresh();

    expect($payment->status)->toBe('processed');
    expect($payment->processed_at)->not->toBeNull();

    $wallet = $user->wallet()->firstOrFail();

    expect((float) $wallet->current_balance)->toBe(1000.0);
    expect((float) $wallet->ledger_balance)->toBe(1000.0);

    $this->assertDatabaseHas('wallet_audits', [
        'wallet_id' => $wallet->id,
        'reference' => $payment->reference,
    ]);
});

it('does not double-credit the wallet when the payment job is processed twice', function () {
    $user = User::factory()->create();

    $walletService = app(\App\Services\WalletService::class);
    $walletService->getOrCreateWalletFor($user);

    $payment = PaymentRequest::create([
        'reference' => 'PAY-IDEMPOTENT-001',
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
        'gateway_id' => 'DEMO-IDEMPOTENT-001',
    ]);

    $payload = [
        'reference' => $payment->reference,
        'amount' => 1000,
        'status' => 'success',
        'gateway_reference' => $payment->gateway_id,
    ];

    ProcessPaymentWebhook::dispatchSync('demo', $payload);
    ProcessPaymentWebhook::dispatchSync('demo', $payload);

    $payment->refresh();
    $wallet = $user->wallet()->firstOrFail();

    expect($payment->status)->toBe('processed');
    expect((float) $wallet->current_balance)->toBe(1000.0);
    expect((float) $wallet->ledger_balance)->toBe(1000.0);

    $this->assertDatabaseCount('wallet_audits', 1);

    $this->assertDatabaseHas('wallet_audits', [
        'wallet_id' => $wallet->id,
        'reference' => $payment->reference,
    ]);
});

it('rejects a successful webhook when the amount does not match the payment', function () {
    $user = User::factory()->create();

    $walletService = app(\App\Services\WalletService::class);
    $walletService->getOrCreateWalletFor($user);

    $payment = PaymentRequest::create([
        'reference' => 'PAY-AMOUNT-MISMATCH-001',
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
        'gateway_id' => 'DEMO-AMOUNT-001',
    ]);

    $payload = [
        'reference' => $payment->reference,
        'amount' => 1500,
        'status' => 'success',
        'gateway_reference' => $payment->gateway_id,
    ];

    ProcessPaymentWebhook::dispatchSync('demo', $payload);

    $payment->refresh();
    $wallet = $user->wallet()->firstOrFail();

    expect($payment->status)->toBe('amount_mismatch');
    expect((float) $wallet->current_balance)->toBe(0.0);
    expect((float) $wallet->ledger_balance)->toBe(0.0);

    $this->assertDatabaseCount('wallet_audits', 0);
});

it('rejects a successful webhook when the gateway reference does not match', function () {
    $user = User::factory()->create();

    $walletService = app(\App\Services\WalletService::class);
    $walletService->getOrCreateWalletFor($user);

    $payment = PaymentRequest::create([
        'reference' => 'PAY-GATEWAY-MISMATCH-001',
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
        'gateway_id' => 'DEMO-EXPECTED-001',
    ]);

    $payload = [
        'reference' => $payment->reference,
        'amount' => 1000,
        'status' => 'success',
        'gateway_reference' => 'DEMO-WRONG-001',
    ];

    ProcessPaymentWebhook::dispatchSync('demo', $payload);

    $payment->refresh();
    $wallet = $user->wallet()->firstOrFail();

    expect($payment->status)->toBe('gateway_mismatch');
    expect((float) $wallet->current_balance)->toBe(0.0);
    expect((float) $wallet->ledger_balance)->toBe(0.0);

    $this->assertDatabaseCount('wallet_audits', 0);
});

it('recovers an unprocessed payment through the webhook retry job', function () {
    $user = User::factory()->create();

    $walletService = app(\App\Services\WalletService::class);
    $walletService->getOrCreateWalletFor($user);

    $payment = PaymentRequest::create([
        'reference' => 'PAY-RETRY-001',
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
        'gateway_id' => 'DEMO-RETRY-001',
    ]);

    $failure = \App\Models\WebhookFailure::create([
        'reference' => $payment->reference,
        'gateway' => 'demo',
        'payload' => [
            'reference' => $payment->reference,
            'amount' => 1000,
            'status' => 'success',
            'gateway_reference' => $payment->gateway_id,
        ],
        'retries' => 0,
    ]);

    \App\Jobs\RetryWebhookPayment::dispatchSync($payment->reference);

    $payment->refresh();

    $wallet = $user->wallet()->firstOrFail();

    expect($payment->status)->toBe('processed');
    expect($payment->processed_at)->not->toBeNull();
    expect((float) $wallet->current_balance)->toBe(1000.0);
    expect((float) $wallet->ledger_balance)->toBe(1000.0);

    $this->assertDatabaseCount('wallet_audits', 1);
    $this->assertDatabaseMissing('webhook_failures', [
        'reference' => $payment->reference,
    ]);
});

it('does not double-credit a payment when the retry job runs twice', function () {
    $user = User::factory()->create();

    $walletService = app(\App\Services\WalletService::class);
    $walletService->getOrCreateWalletFor($user);

    $payment = PaymentRequest::create([
        'reference' => 'PAY-RETRY-IDEMPOTENT-001',
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
        'gateway_id' => 'DEMO-RETRY-IDEMPOTENT-001',
    ]);

    $failure = \App\Models\WebhookFailure::create([
        'reference' => $payment->reference,
        'gateway' => 'demo',
        'payload' => [
            'reference' => $payment->reference,
            'amount' => 1000,
            'status' => 'success',
            'gateway_reference' => $payment->gateway_id,
        ],
        'retries' => 0,
    ]);

    \App\Jobs\RetryWebhookPayment::dispatchSync($payment->reference);

    // The first retry deletes the failure record after successful recovery.
    // Re-running the same job instance should safely detect that the
    // payment has already been processed.
    \App\Jobs\RetryWebhookPayment::dispatchSync($payment->reference);

    $payment->refresh();

    $wallet = $user->wallet()->firstOrFail();

    expect($payment->status)->toBe('processed');
    expect((float) $wallet->current_balance)->toBe(1000.0);
    expect((float) $wallet->ledger_balance)->toBe(1000.0);

    $this->assertDatabaseCount('wallet_audits', 1);
});
