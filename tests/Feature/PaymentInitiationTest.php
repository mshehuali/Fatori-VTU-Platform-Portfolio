<?php

use App\Models\PaymentRequest;
use App\Models\User;

it('creates a demo payment request for a valid amount', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/payments/initiate', [
        'amount' => 1000,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payment_requests', [
        'user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'NGN',
        'payment_method' => 'demo',
        'status' => 'initiated',
    ]);

    $payment = PaymentRequest::where('user_id', $user->id)->first();

    expect($payment)->not->toBeNull();
    expect($payment->reference)->toStartWith('PAY-');
    expect($payment->gateway_id)->toStartWith('DEMO-');
});

it('rejects a payment amount below the minimum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/payments/initiate', [
        'amount' => 50,
    ]);

    $response->assertSessionHasErrors('amount');

    $this->assertDatabaseCount('payment_requests', 0);
});

it('requires authentication to initiate a payment', function () {
    $response = $this->post('/payments/initiate', [
        'amount' => 1000,
    ]);

    $response->assertRedirect('/login');

    $this->assertDatabaseCount('payment_requests', 0);
});