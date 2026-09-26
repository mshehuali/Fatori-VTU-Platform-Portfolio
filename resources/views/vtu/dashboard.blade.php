<x-app-layout>
    {{-- 1. Global Payment Feedback --}}
    @if(isset($payment))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if($payment->status === 'processed')
                <div class="rounded-xl bg-green-100 border border-green-200 text-green-800 px-4 py-3 flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">Wallet funded successfully.</span>
                </div>
            @elseif($payment->status === 'awaiting_admin')
                <div class="rounded-xl bg-yellow-100 border border-yellow-200 text-yellow-800 px-4 py-3 flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">Payment received. Awaiting admin approval.</span>
                </div>
            @endif
        </div>
    @endif

    {{-- 2. Main Dashboard --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

        {{-- Header --}}
        <header class="flex justify-between items-end mb-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    @php
                        $hour = now()->hour;
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                    @endphp
                    {{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }} 👋
                </h1>
                <p class="text-sm text-muted-foreground">Make transactions instantly.</p>
            </div>

            
        </header>

        {{-- Wallet Hero --}}
        <section>
            {{-- ✅ FIX: Using $walletBalance passed from Controller instead of Auth user --}}
            @include('vtu.partials.wallet-hero', [
                'balance' => $walletBalance
            ])
        </section>

        {{-- Quick Services --}}
        <section class="space-y-3">
            <h2 class="text-base font-semibold text-foreground px-1">Quick Services</h2>
            @include('vtu.partials.service-cards')
        </section>

        {{-- Recent Transactions --}}
        @include('vtu.partials.recent-transactions')

        {{-- Stats --}}
        <section class="opacity-80 hover:opacity-100 transition-opacity">
            <h2 class="text-sm font-semibold text-muted-foreground mb-3 px-1">Your Overview</h2>
            @include('vtu.partials.stats-cards')
        </section>

    </div>
</x-app-layout>