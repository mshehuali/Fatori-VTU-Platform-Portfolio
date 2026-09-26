<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        protected PaymentGatewayInterface $gateway,
    ) {
    }

    public function initiate(
        User $user,
        float $amount,
        string $currency = 'NGN',
    ): PaymentRequest {
        $amount = round($amount, 2);

        if ($amount < 100) {
            throw new InvalidArgumentException(
                'Payment amount must be at least 100.'
            );
        }

        return DB::transaction(function () use ($user, $amount, $currency) {
            $payment = PaymentRequest::create([
                'reference' => $this->generateReference(),
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'payment_method' => 'demo',
                'status' => 'initiated',
            ]);

            $result = $this->gateway->initialize($payment);

            $payment->update([
                'status' => $result['status'],
                'gateway_id' => $result['gateway_reference'],
                'gateway_response' => $result['metadata'],
            ]);

            return $payment->fresh();
        });
    }

    private function generateReference(): string
    {
        return 'PAY-'
            . now()->format('YmdHis')
            . '-'
            . strtoupper(bin2hex(random_bytes(4)));
    }
}
