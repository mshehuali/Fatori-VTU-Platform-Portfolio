<x-app-layout>

    @php
        $types = [
            'funding'     => 'Wallet Funding',
            'airtime'     => 'Airtime',
            'data'        => 'Data',
            'cable'       => 'Cable TV',
            'electricity' => 'Electricity',
            'referral_bonus'=> 'Referral Bonus',
        ];
    @endphp
    
    {{-- CSS Override for Date Inputs in Dark Mode --}}
    <style>
        [type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(var(--calendar-invert));
        }
        .dark { --calendar-invert: 1; }
        :root { --calendar-invert: 0; }
    </style>

    {{-- Back --}}
    <div class="text-center mt-4">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-1 text-xs font-medium hover:underline"
           style="color: var(--btn-secondary-text);">
            ← Back to dashboard
        </a>
    </div>
    
    {{-- 🔑 SINGLE ALPINE SCOPE --}}
    <div class="py-8" x-data="transactionPage()">
        <div class="max-w-6xl mx-auto px-4 space-y-5">
    
            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold">Transaction History</h1>
                    <p class="text-xs opacity-70">
                        Wallet funding and VTU transaction records
                    </p>
                </div>
    
                {{-- Actions --}}
                <div class="flex gap-2">
                    {{-- 1. STATEMENT BUTTON (NEW) --}}
                    <button 
                        @click="showStatementModal = true"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Statement
                    </button>

                    {{-- 2. Quick Export Links (Existing) --}}
                    @foreach(['csv'=>'CSV','xlsx'=>'Excel'] as $f => $label)
                        <a href="{{ route('transactions.export', ['format'=>$f] + request()->query()) }}"
                           class="px-3 py-2 rounded-xl text-[11px] border flex items-center gap-1 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                           style="border-color: var(--border);">
                           <span class="opacity-50">⬇</span> {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
    
            {{-- TABLE --}}
            <div class="rounded-2xl border overflow-hidden"
                 style="background-color:var(--background);border-color:var(--border);">
    
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b" style="border-color:var(--border);">
                            <th class="px-3 py-2 text-left">Time</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Description</th>
                            <th class="px-3 py-2 text-left">Reference</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                            <th class="px-3 py-2 text-right">Balance</th>
                            <th class="px-3 py-2 text-right">Status</th>
                        </tr>
                    </thead>
    
                    @foreach($groupedTransactions as $date => $items)
                        <tbody x-data="{ open: true }">
    
                            {{-- DATE HEADER --}}
                            <tr
                                class="cursor-pointer border-b select-none"
                                @click="open = !open"
                                style="background-color:var(--stat-card-bg);border-color:var(--border);"
                            >
                                <td colspan="7" class="px-3 py-2 font-semibold">
                                    {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                                </td>
                            </tr>
    
                            {{-- TRANSACTIONS --}}
                            @foreach($items as $trx)
                                <tr x-show="open" x-cloak class="border-b"
                                    style="border-color:var(--border);">
    
                                    <td class="px-3 py-2">
                                        {{ $trx->created_at->format('h:i A') }}
                                    </td>
    
                                    <td class="px-3 py-2">
                                        {{ ucfirst($trx->source) }}
                                    </td>
    
                                    <td class="px-3 py-2">
                                        {{ $trx->description ?? '—' }}
                                    </td>
    
                                    {{-- Reference --}}
                                    <td class="px-3 py-2 flex items-center gap-2">
                                        <span
                                            class="font-mono underline cursor-pointer"
                                            @click.stop="copyRef('{{ $trx->reference }}')"
                                        >
                                            {{ $trx->reference }}
                                        </span>
    
                                        <button
                                            type="button"
                                            @click.stop="openReceipt('{{ route('transactions.receipt',$trx->reference) }}')"
                                            class="px-2 py-1 rounded-lg text-[10px] font-semibold border hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                            style="border-color:var(--border);"
                                        >
                                            📄 View Receipt
                                        </button>
                                    </td>
    
                                    <td class="px-3 py-2 text-right">
                                        {{ $trx->direction === 'credit' ? '+' : '-' }}
                                        ₦{{ number_format($trx->amount) }}
                                    </td>
    
                                    <td class="px-3 py-2 text-right">
                                        ₦{{ number_format($trx->balance_after) }}
                                    </td>
    
                                    <td class="px-3 py-2 text-right">
                                        <span class="px-2 py-0.5 rounded-full
                                            {{ $trx->status === 'success'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                : ($trx->status === 'failed'
                                                    ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                                    : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400') }}">
                                            {{ ucfirst($trx->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    
        {{-- 🧾 RECEIPT MODAL (Updated Height) --}}
        <div
            x-show="showReceiptModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
        >
            {{-- Increased width to max-w-4xl and height to 90vh --}}
            <div class="w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden relative flex flex-col"
                 style="background-color: var(--background); height: 90vh; min-height: 600px;">
    
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b shrink-0" 
                     style="border-color:var(--border);">
                    <h3 class="font-bold text-lg">Transaction Receipt</h3>
                    <button @click="closeReceipt()" class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-full text-gray-500 hover:text-red-500 transition">
                        &times;
                    </button>
                </div>
    
                {{-- Iframe Container (Flex Grow to fill space) --}}
                <div class="flex-1 bg-white dark:bg-gray-900 w-full relative">
                    <iframe :src="receiptUrl" class="absolute inset-0 w-full h-full border-0"></iframe>
                </div>
            </div>
        </div>

        {{-- 📅 STATEMENT MODAL (NEW) --}}
        <div
            x-show="showStatementModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-3xl shadow-2xl overflow-hidden"
                 style="background-color: var(--background); border: 1px solid var(--border);">
                
                {{-- Header --}}
                <div class="px-6 py-4 border-b flex justify-between items-center" style="border-color:var(--border); background-color:var(--stat-card-bg);">
                    <h3 class="font-bold text-lg">Download Statement</h3>
                    <button @click="showStatementModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Form --}}
                <div class="p-6">
                    <form id="statementForm" method="GET" action="{{ url('/transactions/export/pdf') }}" target="_blank">
                        
                        {{-- Date Range --}}
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-bold opacity-60 uppercase mb-2">From</label>
                                <input type="date" name="date_from" required
                                       class="w-full bg-transparent border rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"
                                       style="border-color:var(--border);">
                            </div>
                            <div>
                                <label class="block text-xs font-bold opacity-60 uppercase mb-2">To</label>
                                <input type="date" name="date_to" required
                                       class="w-full bg-transparent border rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"
                                       style="border-color:var(--border);">
                            </div>
                        </div>

                        {{-- Format --}}
                        <div class="mb-6">
                            <label class="block text-xs font-bold opacity-60 uppercase mb-2">Format</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="fmt" class="peer sr-only" checked @change="updateAction('pdf')">
                                    <div class="p-3 rounded-xl border text-center transition peer-checked:bg-emerald-600 peer-checked:border-emerald-600 peer-checked:text-white hover:bg-gray-50 dark:hover:bg-gray-800"
                                         style="border-color:var(--border);">
                                        <span class="text-sm font-bold">PDF Document</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="fmt" class="peer sr-only" @change="updateAction('xlsx')">
                                    <div class="p-3 rounded-xl border text-center transition peer-checked:bg-emerald-600 peer-checked:border-emerald-600 peer-checked:text-white hover:bg-gray-50 dark:hover:bg-gray-800"
                                         style="border-color:var(--border);">
                                        <span class="text-sm font-bold">Excel (XLSX)</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-900/20 transition active:scale-95">
                            Generate Statement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    
        {{-- 📱 TOAST --}}
        <div
            x-show="toast.show"
            x-transition.opacity
            x-cloak
            class="fixed bottom-20 right-6 z-[9999]
                   bg-slate-900 text-white
                   px-4 py-2 rounded-xl
                   shadow-xl
                   flex items-center gap-2
                   text-sm font-semibold"
        >
            ✔ <span x-text="toast.message"></span>
        </div>
    </div>
    
    {{-- ALPINE --}}
    <script>
    function transactionPage() {
        return {
            showReceiptModal: false,
            showStatementModal: false, // ✅ NEW STATE
            receiptUrl: null,
            toast: { show: false, message: '' },
    
            openReceipt(url) {
                this.receiptUrl = url;
                this.showReceiptModal = true;
            },
    
            closeReceipt() {
                this.showReceiptModal = false;
                this.receiptUrl = null;
            },
    
            copyRef(text) {
                navigator.clipboard.writeText(text).then(() => {
                    this.toast.message = 'Copied';
                    this.toast.show = true;
                    setTimeout(() => this.toast.show = false, 2000);
                });
            },

            // ✅ NEW FUNCTION FOR DYNAMIC FORM ACTION
            updateAction(format) {
                const form = document.getElementById('statementForm');
                const baseUrl = "{{ url('/transactions/export') }}"; 
                form.action = `${baseUrl}/${format}`;
            }
        }
    }
    </script>
    
</x-app-layout>