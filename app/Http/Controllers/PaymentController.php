<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {
    }

    /**
     * Show the portfolio payment demonstration form.
     */
    public function showDepositForm()
    {
        return view('payments.deposit');
    }

    /**
     * Initialize a provider-neutral demo payment.
     *
     * No real payment provider is contacted.
     * Wallet crediting is handled separately through webhook processing.
     */
    public function initiate(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        $payment = $this->paymentService->initiate(
            Auth::user(),
            (float) $data['amount'],
            'NGN',
        );

        return redirect()
            ->route('payments.deposit')
            ->with('status', "Demo payment {$payment->reference} initialized.");
    }

    /**
     * Display the result of a demo payment.
     *
     * This endpoint does not verify or credit the wallet.
     * In a real integration, webhook processing would be responsible
     * for the authoritative payment state transition.
     */
    public function verify(Request $request)
    {
        $reference = $request->query('reference') ?? $request->input('reference');

        if (! $reference) {
            return redirect()
                ->route('payments.deposit')
                ->with('error', 'Missing payment reference.');
        }

        $payment = \App\Models\PaymentRequest::where('reference', $reference)
            ->where('user_id', Auth::id())
            ->first();

        if (! $payment) {
            return redirect()
                ->route('payments.deposit')
                ->with('error', 'Payment request not found.');
        }

        return redirect()
            ->route('payments.deposit')
            ->with('status', "Payment {$payment->reference} is currently {$payment->status}.");
    }
}
