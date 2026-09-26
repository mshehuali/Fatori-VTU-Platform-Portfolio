@php
    use Illuminate\Support\Str;
    $key = session('electricity_form_key') ?? Str::uuid()->toString();
    session(['electricity_form_key' => $key]);
@endphp

<x-app-layout>
    {{-- CSS OVERRIDE --}}
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
            <h1 class="text-lg font-bold">Electricity</h1>
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
            
            <form id="elecForm" method="POST" action="{{ route('electricity.purchase') }}" class="p-6 space-y-8">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $key }}">
                
                {{-- A. Recent Meters (Quick Select) --}}
                @if($recents->isNotEmpty())
                <div>
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-3 block">Quick Select</label>
                    <div class="flex gap-3 overflow-x-auto pb-2 no-scrollbar -mx-6 px-6">
                        @foreach($recents as $recent)
                            <button type="button" 
                                    onclick="fillForm('{{ $recent['meter_no'] }}', '{{ $recent['disco'] }}')"
                                    class="flex flex-col items-center gap-2 min-w-[70px] group">
                                <div class="w-12 h-12 rounded-full bg-secondary border border-border flex items-center justify-center text-lg font-bold group-hover:border-primary transition-colors relative overflow-hidden">
                                    <x-heroicon-m-bolt class="w-5 h-5 text-yellow-500" />
                                    <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-card bg-green-500"></div>
                                </div>
                                <span class="text-[10px] text-muted-foreground truncate max-w-full">{{ $recent['meter_no'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- B. Disco Selector (Logos) --}}
                <div>
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-3 block">Select Disco</label>
                    <input type="hidden" name="disco" id="discoInput" required>
                    
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['IKEDC', 'EKEDC', 'AEDC', 'IBEDC', 'JEDC', 'PHED'] as $disco)
                            <div onclick="selectDisco('{{ $disco }}', this)" 
                                 class="disco-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-3 flex flex-col items-center gap-2 hover:border-yellow-500 transition-all active:scale-95 group">
                                
                                {{-- Placeholder Logo (Initials) --}}
                                <div class="w-10 h-10 rounded-full bg-yellow-500/20 text-yellow-600 flex items-center justify-center font-bold text-xs shadow-sm border border-yellow-500/30">
                                    {{ substr($disco, 0, 2) }}
                                </div>
                                
                                <span class="text-[11px] font-medium">{{ $disco }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- C. Meter Type --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Meter Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="meter_type" value="prepaid" checked class="peer sr-only">
                            <div class="rounded-xl border border-border bg-secondary/30 py-3 text-center text-sm font-medium peer-checked:bg-primary/20 peer-checked:border-primary peer-checked:text-primary transition-all">
                                Prepaid
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="meter_type" value="postpaid" class="peer sr-only">
                            <div class="rounded-xl border border-border bg-secondary/30 py-3 text-center text-sm font-medium peer-checked:bg-primary/20 peer-checked:border-primary peer-checked:text-primary transition-all">
                                Postpaid
                            </div>
                        </label>
                    </div>
                </div>

                {{-- D. Meter Number --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Meter Number</label>
                    <div class="relative">
                        <input type="tel" name="meter_no" id="meterInput" placeholder="Enter meter number" required
                               class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl pl-4 pr-12 py-3.5 text-lg font-mono tracking-wide 
                                      text-gray-900 dark:text-white 
                                      focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder:text-muted-foreground/50">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-muted-foreground">
                            <x-heroicon-m-qr-code class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                {{-- E. Phone Number --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Phone Number</label>
                    <input type="tel" name="phone" value="{{ auth()->user()->phone }}" required
                           class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl px-4 py-3.5 text-base font-mono 
                                  text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                </div>

                {{-- F. Amount --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Amount (₦)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-muted-foreground">₦</span>
                        <input type="number" name="amount" placeholder="1000" required min="500"
                               class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl pl-10 pr-4 py-3.5 text-2xl font-bold 
                                      text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
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

                {{-- G. Submit --}}
                <div class="pt-4 border-t border-border mt-4">
                    <button type="submit" id="submitBtn" disabled
                            class="w-full relative group overflow-hidden rounded-2xl p-[1px] shadow-xl shadow-yellow-500/20 disabled:shadow-none transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-orange-600 group-hover:from-yellow-400 group-hover:to-orange-500 transition-colors"></div>
                        <div class="relative bg-gradient-to-r from-yellow-500 to-orange-600 group-disabled:bg-none group-disabled:bg-gray-700/50 h-full rounded-2xl px-6 py-4 flex items-center justify-center gap-3">
                            <span class="text-lg font-bold text-white group-disabled:text-white/50">Buy Token</span>
                            <x-heroicon-m-bolt class="w-5 h-5 text-white animate-pulse group-disabled:hidden" />
                        </div>
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- JS LOGIC --}}
    <script>
        function selectDisco(disco, element = null) {
            document.getElementById('discoInput').value = disco;
            
            // Visual Selection
            document.querySelectorAll('.disco-card').forEach(el => {
                el.classList.remove('ring-2', 'ring-primary', 'bg-secondary');
                el.classList.add('bg-secondary/30');
            });

            if (element) {
                element.classList.remove('bg-secondary/30');
                element.classList.add('ring-2', 'ring-primary', 'bg-secondary');
            } else {
                 // Auto-select for Quick Pick
                 document.querySelectorAll('.disco-card').forEach(el => {
                    if(el.innerText.toUpperCase().includes(disco.toUpperCase())) {
                        el.classList.remove('bg-secondary/30');
                        el.classList.add('ring-2', 'ring-primary', 'bg-secondary');
                    }
                 });
            }
            
            // Enable button
            checkForm();
        }

        function fillForm(meter, disco) {
            document.getElementById('meterInput').value = meter;
            selectDisco(disco);
        }

        function checkForm() {
            const disco = document.getElementById('discoInput').value;
            const btn = document.getElementById('submitBtn');
            if (disco) {
                btn.disabled = false;
            }
        }

        // Loading State
        document.getElementById('elecForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            if (btn.disabled) { e.preventDefault(); return; }
            btn.disabled = true;
            btn.innerHTML = `<div class="bg-gray-700/20 h-full w-full flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...</div>`;
        });
    </script>
</x-app-layout>