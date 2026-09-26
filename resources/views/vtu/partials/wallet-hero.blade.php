@props(['balance' => 0])

@php
    // Normalize balance once (VERY IMPORTANT)
    $balance = is_numeric($balance) ? (float) $balance : 0.0;
    $masked  = '₦ • • • • •';
    $initialHidden = session('hide_balance', false);
@endphp

<div
    x-data="{
        hidden: {{ $initialHidden ? 'true' : 'false' }},
        toggle() {
            this.hidden = !this.hidden;
        }
    }"
    class="relative w-full"
>
    {{-- Card --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0f172a] to-[#334155] text-white shadow-xl shadow-emerald-500/10">

        {{-- Decorative --}}
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-500/20 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-teal-500/10 rounded-full blur-2xl"></div>

        <div class="p-6 relative z-10">

            {{-- Header --}}
            <div class="flex justify-between items-center mb-4 opacity-80">
                <span class="text-xs font-medium tracking-wider uppercase">
                    Available Balance
                </span>

                <button @click="toggle" class="p-1 hover:text-emerald-300 transition">
                    <template x-if="hidden">
                        <x-heroicon-o-eye class="w-5 h-5" />
                    </template>
                    <template x-if="!hidden">
                        <x-heroicon-o-eye-slash class="w-5 h-5" />
                    </template>
                </button>
            </div>

            {{-- Balance --}}
            <div class="flex items-baseline gap-1 mb-6">
                <span
                    x-show="hidden"
                    x-cloak
                    class="text-3xl font-mono font-bold tracking-widest opacity-80"
                >
                    {{ $masked }}
                </span>

                <span
                    x-show="!hidden"
                    x-cloak
                    class="text-3xl font-bold tracking-tight"
                >
                    ₦{{ number_format($balance, 2) }}
                </span>
            </div>

            {{-- Actions --}}
            <div class="grid grid-cols-2 gap-3 mt-2">
                <a
                    href="{{ route('wallet.fund.form') }}"
                    class="flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white py-2.5 rounded-xl font-semibold text-sm transition shadow-lg shadow-emerald-500/30"
                >
                    <x-heroicon-s-plus class="w-4 h-4" />
                    Fund Wallet
                </a>

                <a
                    href="{{ route('transactions.index') }}"
                    class="flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white py-2.5 rounded-xl font-medium text-sm transition"
                >
                    <x-heroicon-o-clock class="w-4 h-4" />
                    History
                </a>
            </div>

        </div>
    </div>
</div>
