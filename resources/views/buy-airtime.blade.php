@php
    use Illuminate\Support\Str;
    $key = session('airtime_form_key') ?? Str::uuid()->toString();
    session(['airtime_form_key' => $key]);
@endphp

<x-app-layout>
    {{-- CSS OVERRIDE: Fixes the white background on autofill --}}
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
            <h1 class="text-lg font-bold">Buy Airtime</h1>
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

        {{-- 3. THE MAIN CARD --}}
        <div class="relative overflow-hidden rounded-3xl bg-card border border-border shadow-xl">
            
            <form id="airtimeForm" method="POST" action="{{ route('airtime.purchase') }}" class="p-6 space-y-8">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $key }}">
                
                {{-- A. Recent Beneficiaries (Quick Select) --}}
                @if($recents->isNotEmpty())
                <div>
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-3 block">Quick Select</label>
                    <div class="flex gap-3 overflow-x-auto pb-2 no-scrollbar -mx-6 px-6">
                        @foreach($recents as $recent)
                            <button type="button" 
                                    onclick="fillForm('{{ $recent['phone'] }}', '{{ $recent['network'] }}')"
                                    class="flex flex-col items-center gap-2 min-w-[70px] group">
                                <div class="w-12 h-12 rounded-full bg-secondary border border-border flex items-center justify-center text-lg font-bold group-hover:border-primary transition-colors relative overflow-hidden">
                                    {{ substr($recent['name'], -2) }}
                                    <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-card 
                                        {{ $recent['network'] == 'MTN' ? 'bg-yellow-400' : ($recent['network'] == 'Airtel' ? 'bg-red-500' : 'bg-green-500') }}">
                                    </div>
                                </div>
                                <span class="text-[10px] text-muted-foreground truncate max-w-full">{{ $recent['phone'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- B. Network Selector --}}
                <div>
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-3 block">Select Network</label>
                    <input type="hidden" name="network" id="networkInput" required>
                    <div class="grid grid-cols-4 gap-3">
                        <div onclick="selectNetwork('MTN', this)" class="network-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-2 flex flex-col items-center gap-1 hover:border-yellow-400 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center text-black font-bold text-xs shadow-sm">MTN</div>
                            <span class="text-[10px] font-medium">MTN</span>
                        </div>
                        <div onclick="selectNetwork('Airtel', this)" class="network-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-2 flex flex-col items-center gap-1 hover:border-red-500 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-red-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">AIR</div>
                            <span class="text-[10px] font-medium">Airtel</span>
                        </div>
                        <div onclick="selectNetwork('Glo', this)" class="network-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-2 flex flex-col items-center gap-1 hover:border-green-500 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">GLO</div>
                            <span class="text-[10px] font-medium">Glo</span>
                        </div>
                        <div onclick="selectNetwork('9mobile', this)" class="network-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-2 flex flex-col items-center gap-1 hover:border-emerald-800 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-emerald-900 flex items-center justify-center text-white font-bold text-xs shadow-sm">9M</div>
                            <span class="text-[10px] font-medium">9mobile</span>
                        </div>
                    </div>
                </div>

                {{-- C. Phone Number Input --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Phone Number</label>
                    <div class="relative">
                        <input type="tel" name="phone" id="phoneInput" placeholder="0801 234 5678" required maxlength="11"
                               class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl px-4 py-3.5 text-lg font-mono tracking-wide 
                                      text-gray-900 dark:text-white 
                                      focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder:text-muted-foreground/50">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-card rounded-lg border border-border text-muted-foreground">
                            <x-heroicon-m-user class="w-4 h-4" />
                        </div>
                    </div>
                </div>

                {{-- D. Amount Input + Speed Chips --}}
                <div class="space-y-3">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Amount (₦)</label>
                    
                    {{-- Input Field --}}
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-muted-foreground">₦</span>
                        <input type="number" name="amount" id="amountInput" placeholder="100" required min="50"
                               class="w-full bg-gray-50 dark:bg-gray-900 border border-border rounded-xl pl-10 pr-4 py-3.5 text-2xl font-bold 
                                      text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    </div>

                    {{-- Speed Chips --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach([100, 200, 500, 1000] as $amt)
                        <button type="button" onclick="setAmount({{ $amt }})" 
                                class="px-3 py-1.5 rounded-lg border border-border bg-secondary/30 text-xs font-medium hover:border-primary hover:text-primary active:scale-95 transition-all">
                            ₦{{ $amt }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- E. Submit Button --}}
                <div class="pt-4 border-t border-border mt-4">
                    <button type="submit" id="submitBtn"
                            class="w-full relative group overflow-hidden rounded-2xl p-[1px] shadow-xl shadow-emerald-500/20 transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-600 group-hover:from-emerald-400 group-hover:to-teal-500 transition-colors"></div>
                        <div class="relative bg-gradient-to-r from-emerald-500 to-teal-600 h-full rounded-2xl px-6 py-4 flex items-center justify-center gap-3">
                            <span class="text-lg font-bold text-white">Top Up Airtime</span>
                            <x-heroicon-m-paper-airplane class="w-5 h-5 text-white animate-pulse" />
                        </div>
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
    <script>
        function selectNetwork(network, element = null) {
            document.getElementById('networkInput').value = network;
            
            // 1. Reset all
            document.querySelectorAll('.network-card').forEach(el => {
                el.classList.remove('ring-2', 'ring-primary', 'bg-secondary');
                el.classList.add('bg-secondary/30');
            });

            // 2. Highlight selected
            if (element) {
                element.classList.remove('bg-secondary/30');
                element.classList.add('ring-2', 'ring-primary', 'bg-secondary');
            } else {
                 // Auto-select by text match if no element passed
                 document.querySelectorAll('.network-card').forEach(el => {
                    if(el.innerText.toUpperCase().includes(network.toUpperCase())) {
                        el.classList.remove('bg-secondary/30');
                        el.classList.add('ring-2', 'ring-primary', 'bg-secondary');
                    }
                 });
            }
        }

        function setAmount(val) {
            document.getElementById('amountInput').value = val;
        }

        function fillForm(phone, network) {
            document.getElementById('phoneInput').value = phone;
            selectNetwork(network);
        }

        // Auto-Detect Network Logic
        const phoneInput = document.getElementById('phoneInput');
        const prefixMap = {
            'MTN':     ['0803','0806','0703','0706','0810','0813','0814','0816','0903','0906','0913','0916'],
            'Airtel':  ['0802','0808','0708','0812','0902','0907','0901','0912'],
            'Glo':     ['0805','0807','0705','0811','0815','0905','0915'],
            '9mobile': ['0809','0817','0818','0909','0908'],
        };

        phoneInput.addEventListener('input', function() {
            const val = this.value.replace(/\D/g, '');
            if(val.length >= 4) {
                const prefix = '0' + val.substring(val.length - 10, val.length - 7); // Handle 080... format
                // Simple 4 digit check
                const simplePrefix = val.substring(0, 4); 
                
                for (const [net, prefixes] of Object.entries(prefixMap)) {
                    if (prefixes.includes(simplePrefix) || prefixes.includes(prefix)) {
                        selectNetwork(net);
                        break;
                    }
                }
            }
        });

        // Loading State
        document.getElementById('airtimeForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = `<div class="bg-gray-700/20 h-full w-full flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...</div>`;
        });
    </script>
</x-app-layout>