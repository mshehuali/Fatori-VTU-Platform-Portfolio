@php
    use Illuminate\Support\Str;
    $key = session('cable_form_key') ?? Str::uuid()->toString();
    session(['cable_form_key' => $key]);
@endphp

<x-app-layout>
    {{-- CSS OVERRIDE: Autofill Fix --}}
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
            <h1 class="text-lg font-bold">Cable TV</h1>
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
            
            <form id="cableForm" method="POST" action="{{ route('cable.purchase') }}" class="p-6 space-y-8">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $key }}">
                
                {{-- A. Quick Select (History) --}}
                @if($recents->isNotEmpty())
                <div>
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-3 block">Quick Select</label>
                    <div class="flex gap-3 overflow-x-auto pb-2 no-scrollbar -mx-6 px-6">
                        @foreach($recents as $recent)
                            <button type="button" 
                                    onclick="fillForm('{{ $recent['smartcard'] }}', '{{ $recent['provider'] }}')"
                                    class="flex flex-col items-center gap-2 min-w-[70px] group">
                                <div class="w-12 h-12 rounded-full bg-secondary border border-border flex items-center justify-center text-lg font-bold group-hover:border-primary transition-colors relative overflow-hidden">
                                    {{ substr($recent['provider'], 0, 1) }}
                                    <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-card 
                                        {{ $recent['provider'] == 'DSTV' ? 'bg-blue-500' : ($recent['provider'] == 'GOTV' ? 'bg-green-500' : 'bg-purple-500') }}">
                                    </div>
                                </div>
                                <span class="text-[10px] text-muted-foreground truncate max-w-full">{{ $recent['smartcard'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- B. Provider Selector --}}
                <div>
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-3 block">Select Provider</label>
                    <input type="hidden" name="provider" id="providerInput" required>
                    
                    <div class="grid grid-cols-3 gap-3">
                        {{-- DSTV --}}
                        <div onclick="selectProvider('DSTV', this)" 
                             class="provider-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-3 flex flex-col items-center gap-2 hover:border-blue-500 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xs shadow-sm shadow-blue-500/20">DSTV</div>
                            <span class="text-[11px] font-medium">DSTV</span>
                        </div>

                        {{-- GOtv --}}
                        <div onclick="selectProvider('GOTV', this)" 
                             class="provider-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-3 flex flex-col items-center gap-2 hover:border-green-500 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold text-xs shadow-sm shadow-green-500/20">GOtv</div>
                            <span class="text-[11px] font-medium">GOtv</span>
                        </div>

                        {{-- StarTimes --}}
                        <div onclick="selectProvider('StarTimes', this)" 
                             class="provider-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-3 flex flex-col items-center gap-2 hover:border-purple-500 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-xs shadow-sm shadow-purple-600/20">STAR</div>
                            <span class="text-[11px] font-medium">StarTimes</span>
                        </div>
                    </div>
                </div>

                {{-- C. Smartcard Number --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">IUC / Smartcard Number</label>
                    <div class="relative">
                        <input type="tel" name="smartcard" id="smartcardInput" placeholder="Enter number on decoder" required
                               class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl pl-4 pr-12 py-3.5 text-lg font-mono tracking-wide 
                                      text-gray-900 dark:text-white 
                                      focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder:text-muted-foreground/50">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-muted-foreground">
                            <x-heroicon-m-tv class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                {{-- D. Package Selector --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Package</label>
                    <div class="relative">
                        <select name="package" id="packageSelect" required onchange="updatePrice()"
                                class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl px-4 py-3.5 text-base appearance-none bg-none
                                       text-gray-900 dark:text-white
                                       focus:ring-2 focus:ring-primary focus:border-transparent transition-all cursor-pointer">
                            <option value="" disabled selected>Select Provider First</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
                            <x-heroicon-m-chevron-up-down class="w-5 h-5" />
                        </div>
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

                {{-- E. Total & Submit --}}
                <div class="pt-4 border-t border-border mt-4">
                    <div class="flex items-end justify-between mb-6 px-1">
                        <span class="text-sm font-medium text-muted-foreground">Total to Pay</span>
                        <div class="text-right">
                            <span class="text-3xl font-bold text-foreground tracking-tight" id="priceDisplay">₦0.00</span>
                            <input type="hidden" name="amount" id="amountInput">
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" disabled
                            class="w-full relative group overflow-hidden rounded-2xl p-[1px] shadow-xl shadow-purple-500/20 disabled:shadow-none transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-indigo-600 group-hover:from-purple-400 group-hover:to-indigo-500 transition-colors"></div>
                        <div class="relative bg-gradient-to-r from-purple-500 to-indigo-600 group-disabled:bg-none group-disabled:bg-gray-700/50 h-full rounded-2xl px-6 py-4 flex items-center justify-center gap-3">
                            <span class="text-lg font-bold text-white group-disabled:text-white/50">Pay Subscription</span>
                            <x-heroicon-m-play-circle class="w-5 h-5 text-white animate-pulse group-disabled:hidden" />
                        </div>
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- JS LOGIC --}}
    <script>
        // MOCK PACKAGES (In production, replace with API fetched data)
        const cablePackages = {
            'DSTV': [
                { name: 'Padi', price: 2500 },
                { name: 'Yanga', price: 4200 },
                { name: 'Confam', price: 7400 },
                { name: 'Compact', price: 12500 },
                { name: 'Compact Plus', price: 19800 },
                { name: 'Premium', price: 29500 },
            ],
            'GOTV': [
                { name: 'Smallie', price: 1300 },
                { name: 'Jinja', price: 2700 },
                { name: 'Jolli', price: 3950 },
                { name: 'Max', price: 5700 },
                { name: 'Supa', price: 7600 },
            ],
            'StarTimes': [
                { name: 'Nova', price: 1200 },
                { name: 'Basic', price: 2600 },
                { name: 'Smart', price: 3500 },
                { name: 'Classic', price: 3800 },
                { name: 'Super', price: 6500 },
            ]
        };

        function selectProvider(provider, element = null) {
            document.getElementById('providerInput').value = provider;
            
            // Visual Selection
            document.querySelectorAll('.provider-card').forEach(el => {
                el.classList.remove('ring-2', 'ring-primary', 'bg-secondary');
                el.classList.add('bg-secondary/30');
            });

            if (element) {
                element.classList.remove('bg-secondary/30');
                element.classList.add('ring-2', 'ring-primary', 'bg-secondary');
            } else {
                 // Auto-select for Quick Pick
                 document.querySelectorAll('.provider-card').forEach(el => {
                    if(el.innerText.toUpperCase().includes(provider.toUpperCase())) {
                        el.classList.remove('bg-secondary/30');
                        el.classList.add('ring-2', 'ring-primary', 'bg-secondary');
                    }
                 });
            }

            // Populate Packages
            const pkgSelect = document.getElementById('packageSelect');
            pkgSelect.innerHTML = '<option value="" disabled selected>Select Package</option>';

            if (cablePackages[provider]) {
                cablePackages[provider].forEach(pkg => {
                    const option = document.createElement('option');
                    option.value = pkg.name;
                    option.dataset.price = pkg.price;
                    option.textContent = `${pkg.name} - ₦${new Intl.NumberFormat().format(pkg.price)}`;
                    pkgSelect.appendChild(option);
                });
            } else {
                pkgSelect.innerHTML = '<option value="" disabled>No packages available</option>';
            }
            updatePrice();
        }

        function updatePrice() {
            const pkgSelect = document.getElementById('packageSelect');
            const priceDisplay = document.getElementById('priceDisplay');
            const amountInput = document.getElementById('amountInput');
            const submitBtn = document.getElementById('submitBtn');

            if (pkgSelect.selectedIndex > 0) {
                const selectedOption = pkgSelect.options[pkgSelect.selectedIndex];
                const price = selectedOption.dataset.price;
                priceDisplay.textContent = '₦' + new Intl.NumberFormat().format(price);
                amountInput.value = price;
                submitBtn.disabled = false;
            } else {
                priceDisplay.textContent = '₦0.00';
                amountInput.value = 0;
                submitBtn.disabled = true;
            }
        }

        function fillForm(smartcard, provider) {
            document.getElementById('smartcardInput').value = smartcard;
            selectProvider(provider);
        }

        // Loading State
        document.getElementById('cableForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            if (btn.disabled) { e.preventDefault(); return; }
            btn.disabled = true;
            btn.innerHTML = `<div class="bg-gray-700/20 h-full w-full flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...</div>`;
        });
    </script>
</x-app-layout>