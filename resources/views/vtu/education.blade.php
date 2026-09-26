@php
    use Illuminate\Support\Str;
    $key = session('education_form_key') ?? Str::uuid()->toString();
    session(['education_form_key' => $key]);
@endphp

<x-app-layout>
    {{-- CSS Override for Autofill --}}
    <style>
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #111827 inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        html:not(.dark) input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px #f9fafb inset !important;
            -webkit-text-fill-color: #111827 !important;
        }
    </style>

    <div class="max-w-md mx-auto px-4 py-6">

        {{-- 1. Header & Back Button --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('dashboard') }}" class="group flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                <div class="w-8 h-8 rounded-full bg-card border border-border flex items-center justify-center group-hover:border-primary/50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </div>
                <span>Back</span>
            </a>
            <h1 class="text-lg font-bold">Education Pins</h1>
            <div class="w-8"></div>
        </div>

        {{-- 2. Success/Error Feedback --}}
        @if (session('success') || session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="mb-6 rounded-2xl p-4 flex items-start gap-3 shadow-lg {{ session('error') ? 'bg-red-500/10 border border-red-500/20 text-red-500' : 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-500' }}">
                <div class="mt-0.5">
                    @if(session('error')) <x-heroicon-m-exclamation-circle class="w-5 h-5"/> @else <x-heroicon-m-check-circle class="w-5 h-5"/> @endif
                </div>
                <div class="text-sm font-medium leading-relaxed">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        @endif

        {{-- 3. MAIN CARD --}}
        <div class="relative overflow-hidden rounded-3xl bg-card border border-border shadow-xl">
            
            <form id="eduForm" method="POST" action="{{ route('education.buy') }}" class="p-6 space-y-8">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $key }}">
                
                {{-- A. Exam Type Selector (Visual Cards) --}}
                <div>
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-3 block">Select Exam</label>
                    <input type="hidden" name="exam_type" id="examTypeInput" required>
                    
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($services as $service)
                            <div onclick="selectExam('{{ $service['code'] }}', {{ $service['price'] }}, this)" 
                                 class="exam-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-3 flex flex-col items-center gap-2 hover:border-{{ $service['color'] }}-500 transition-all active:scale-95 group relative overflow-hidden">
                                
                                {{-- Icon Bubble --}}
                                <div class="w-12 h-12 rounded-full bg-{{ $service['color'] }}-500/20 text-{{ $service['color'] }}-600 flex items-center justify-center font-bold text-lg shadow-sm border border-{{ $service['color'] }}-500/30">
                                    {{ $service['icon'] }}
                                </div>
                                
                                {{-- Name --}}
                                <span class="text-[10px] font-bold uppercase tracking-wide opacity-80 group-hover:opacity-100">{{ $service['name'] }}</span>
                                
                                {{-- Price Tag --}}
                                <div class="absolute top-1 right-1 bg-card/80 backdrop-blur px-1.5 py-0.5 rounded text-[9px] font-mono text-muted-foreground border border-border">
                                    ₦{{ number_format($service['price']) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- B. Quantity Selector --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Quantity</label>
                    <div class="flex items-center gap-4">
                        <button type="button" onclick="adjustQty(-1)" class="w-12 h-12 rounded-xl bg-secondary hover:bg-secondary/80 flex items-center justify-center text-xl font-bold border border-border transition">-</button>
                        
                        <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="10" readonly
                               class="flex-1 bg-gray-50 dark:bg-gray-900 border border-border rounded-xl py-3 text-center text-xl font-bold text-gray-900 dark:text-white focus:ring-0 focus:border-border">
                        
                        <button type="button" onclick="adjustQty(1)" class="w-12 h-12 rounded-xl bg-secondary hover:bg-secondary/80 flex items-center justify-center text-xl font-bold border border-border transition">+</button>
                    </div>
                </div>

                {{-- C. Delivery Details (Optional) --}}
                <div class="space-y-4 pt-2">
                     <div class="space-y-1">
                        <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Delivery Email (Optional)</label>
                        <input type="email" name="delivery_email" placeholder="Send PINs to..." value="{{ auth()->user()->email }}"
                               class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    </div>
                </div>

                                          {{-- TRANSACTION PIN --}}
                                          <div class="pt-4 border-t border-border mt-4">
                                            <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-2 block">Transaction PIN</label>
                                            <div class="relative">
                                                <input type="password" name="transaction_pin" placeholder="Enter 4-digit PIN" required maxlength="4" inputmode="numeric"
                                                    class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl px-4 py-3.5 text-lg font-bold tracking-widest text-center
                                                            text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                                                    <x-heroicon-m-lock-closed class="w-5 h-5" />
                                                </div>
                                            </div>
                                        </div>

                {{-- D. Total & Submit --}}
                <div class="pt-4 border-t border-border mt-4">
                    <div class="flex items-end justify-between mb-6 px-1">
                        <span class="text-sm font-medium text-muted-foreground">Total Cost</span>
                        <div class="text-right">
                            <span class="text-3xl font-bold text-foreground tracking-tight" id="priceDisplay">₦0.00</span>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" disabled
                            class="w-full relative group overflow-hidden rounded-2xl p-[1px] shadow-xl shadow-yellow-500/20 disabled:shadow-none transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-orange-600 group-hover:from-yellow-400 group-hover:to-orange-500 transition-colors"></div>
                        <div class="relative bg-gradient-to-r from-yellow-500 to-orange-600 group-disabled:bg-none group-disabled:bg-gray-700/50 h-full rounded-2xl px-6 py-4 flex items-center justify-center gap-3">
                            <span class="text-lg font-bold text-white group-disabled:text-white/50">Purchase PINs</span>
                            <x-heroicon-m-academic-cap class="w-5 h-5 text-white animate-bounce group-disabled:hidden" />
                        </div>
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- JS LOGIC --}}
    <script>
        let currentPrice = 0;

        function selectExam(code, price, element) {
            document.getElementById('examTypeInput').value = code;
            currentPrice = price;
            
            // Visual Selection
            document.querySelectorAll('.exam-card').forEach(el => {
                el.classList.remove('ring-2', 'ring-primary', 'bg-secondary');
                el.classList.add('bg-secondary/30');
            });

            element.classList.remove('bg-secondary/30');
            element.classList.add('ring-2', 'ring-primary', 'bg-secondary');
            
            updateTotal();
        }

        function adjustQty(change) {
            const input = document.getElementById('qtyInput');
            let val = parseInt(input.value) + change;
            if(val < 1) val = 1;
            if(val > 10) val = 10; // Max limit
            input.value = val;
            updateTotal();
        }

        function updateTotal() {
            const qty = parseInt(document.getElementById('qtyInput').value);
            const total = currentPrice * qty;
            const submitBtn = document.getElementById('submitBtn');
            const priceDisplay = document.getElementById('priceDisplay');

            if (currentPrice > 0) {
                priceDisplay.textContent = '₦' + new Intl.NumberFormat().format(total);
                submitBtn.disabled = false;
            } else {
                priceDisplay.textContent = '₦0.00';
                submitBtn.disabled = true;
            }
        }

        // Loading State
        document.getElementById('eduForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            if (btn.disabled) { e.preventDefault(); return; }
            btn.disabled = true;
            btn.innerHTML = `<div class="bg-gray-700/20 h-full w-full flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...</div>`;
        });
    </script>
</x-app-layout>