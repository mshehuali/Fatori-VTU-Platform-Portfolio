<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected PaymentService $paymentService,
    ) {
        $this->middleware('auth');
    }

    /**
     * Display the wallet funding demonstration form.
     */
    public function showFundForm()
    {
        return view('wallet.fund');
    }

    /**
     * Initialize a provider-neutral demo payment.
     *
     * No real payment provider is contacted and no wallet balance
     * is changed at this stage.
     */
    public function initiateFunding(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        $payment = $this->paymentService->initiate(
            Auth::user(),
            (float) $data['amount'],
            'NGN',
        );

        return back()->with(
            'status',
            "Demo payment {$payment->reference} initialized."
        );
    }

    /**
     * Display the authenticated user's wallet.
     */
    public function show()
    {
        $wallet = $this->walletService->getOrCreateWalletFor(Auth::user());

        return view('wallet.index', compact('wallet'));
    }
}
