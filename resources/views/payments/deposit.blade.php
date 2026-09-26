<x-app-layout>
    <div class="max-w-md mx-auto py-12 px-4">

        <div class="bg-card border border-border rounded-2xl p-6 space-y-6">
            <h1 class="text-xl font-semibold text-foreground">
                Fund Wallet
            </h1>

            <div>
                <label class="text-xs font-semibold opacity-80 block mb-1">
                    Amount (NGN)
                </label>

                <input
                    type="text"
                    readonly
                    value="{{ number_format($payment->amount, 2) }}"
                    class="w-full rounded-lg border border-border px-4 py-3
                           bg-background text-foreground opacity-80"
                />
            </div>

            <form method="POST" action="{{ route('payments.initiate') }}">
                @csrf

                <input type="hidden" name="reference" value="{{ $payment->reference }}">
                <input type="hidden" name="amount" value="{{ $payment->amount }}">

                <button
                    type="submit"
                    class="w-full btn-primary py-3 rounded-xl font-semibold">
                    Proceed with Demo Payment
                </button>
            </form>

            <p class="text-xs opacity-60 text-center">
                Minimum NGN 100. The payment is processed through the demo payment gateway.
            </p>
        </div>

    </div>
</x-app-layout>

