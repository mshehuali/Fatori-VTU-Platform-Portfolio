@php
    use Illuminate\Support\Str;
    $key = session('data_form_key') ?? Str::uuid()->toString();
    session(['data_form_key' => $key]);
@endphp

<x-app-layout>

    <div class="max-w-md mx-auto px-4 py-6">
        
        {{-- 1. Header & Back Button --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('dashboard') }}" class="group flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                <div class="w-8 h-8 rounded-full bg-card border border-border flex items-center justify-center group-hover:border-primary/50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </div>
                <span>Back</span>
            </a>
            <h1 class="text-lg font-bold">Buy Data Bundle</h1>
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
            
            <form id="dataForm" method="POST" action="{{ route('data.purchase') }}" class="p-6 space-y-8">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $key }}">
                
                {{-- A. Recent Beneficiaries --}}
                @if(isset($recents) && $recents->isNotEmpty())
                <div>
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-3 block">Quick Select</label>
                    <div class="flex gap-3 overflow-x-auto pb-2 no-scrollbar -mx-6 px-6">
                        @foreach($recents as $recent)
                            <button type="button" 
                                    onclick="fillForm('{{ $recent['phone'] }}', '{{ $recent['network'] }}')"
                                    class="flex flex-col items-center gap-2 min-w-[70px] group">
                                <div class="w-12 h-12 rounded-full bg-secondary border border-border flex items-center justify-center text-lg font-bold group-hover:border-primary transition-colors relative overflow-hidden">
                                    {{ substr($recent['name'], 0, 1) }}
                                    <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-card 
                                        {{ $recent['network'] == 'MTN' ? 'bg-yellow-400' : ($recent['network'] == 'airtel' ? 'bg-red-500' : 'bg-green-500') }}">
                                    </div>
                                </div>
                                <span class="text-[10px] text-muted-foreground truncate max-w-full">{{ $recent['name'] }}</span>
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
                        <div onclick="selectNetwork('AIRTEL', this)" class="network-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-2 flex flex-col items-center gap-1 hover:border-red-500 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-red-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">AIR</div>
                            <span class="text-[10px] font-medium">Airtel</span>
                        </div>
                        <div onclick="selectNetwork('GLO', this)" class="network-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-2 flex flex-col items-center gap-1 hover:border-green-500 transition-all active:scale-95 group">
                            <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">GLO</div>
                            <span class="text-[10px] font-medium">Glo</span>
                        </div>
                        <div onclick="selectNetwork('9MOBILE', this)" class="network-card cursor-pointer rounded-2xl border border-border bg-secondary/30 p-2 flex flex-col items-center gap-1 hover:border-emerald-800 transition-all active:scale-95 group">
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
                               class="w-full bg-background text-foreground dark:bg-background dark:text-foreground border border-border rounded-xl px-4 py-3.5 text-lg font-mono tracking-wide focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder:text-muted-foreground/50 dark:placeholder:text-muted-foreground/50">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-card rounded-lg border border-border text-muted-foreground">
                            <x-heroicon-m-user class="w-4 h-4" />
                        </div>
                    </div>
                </div>

                {{-- D. Data Plan Selection (UPDATED: name="plan_id") --}}
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase">Data Plan</label>
                    <div class="relative">
                        <select name="plan_id" id="planSelect" required onchange="updatePrice()"
                                class="w-full bg-card text-foreground border border-border rounded-xl px-4 py-3.5 text-base appearance-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all cursor-pointer h-14">
                            <option value="" disabled selected>Select Network First</option>
                        </select>
                    </div>
                </div>

                {{-- E. Amount, PIN & Submit --}}
                <div class="pt-4 border-t border-border mt-4">
                    {{-- Total Display --}}
                    <div class="flex items-end justify-between mb-6 px-1">
                        <span class="text-sm font-medium text-muted-foreground">Total to Pay</span>
                        <div class="text-right">
                            <span class="text-3xl font-bold text-foreground tracking-tight" id="priceDisplay">₦0.00</span>
                            {{-- REMOVED: Hidden amount input to prevent tampering. Price is handled on backend. --}}
                        </div>
                    </div>

                    {{-- TRANSACTION PIN --}}
                    <div class="mb-6">
                        <label class="text-xs font-semibold text-muted-foreground tracking-wider uppercase mb-2 block">Transaction PIN</label>
                        <div class="relative">
                            <input type="password" name="transaction_pin" placeholder="Enter 4-digit PIN" required maxlength="4" inputmode="numeric"
                                   class="w-full bg-background text-foreground dark:bg-background dark:text-foreground border border-border rounded-xl px-4 py-3.5 text-lg font-bold tracking-widest text-center focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                                <x-heroicon-m-lock-closed class="w-5 h-5" />
                            </div>
                        </div>
                    </div>

                    {{-- SUBMIT BUTTON --}}
                    <button type="submit" id="submitBtn" disabled
                            class="w-full relative group overflow-hidden rounded-2xl p-[1px] shadow-xl shadow-emerald-500/20 disabled:shadow-none transition-all duration-300">
                        
                        {{-- Gradient Border/Background --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-600 group-hover:from-emerald-400 group-hover:to-teal-500 transition-colors"></div>
                        
                        {{-- Button Content --}}
                        <div class="relative bg-gradient-to-r from-emerald-500 to-teal-600 group-disabled:bg-none group-disabled:bg-gray-700/50 h-full rounded-2xl px-6 py-4 flex items-center justify-center gap-3">
                            <span class="text-lg font-bold text-white group-disabled:text-white/50">Buy Data Bundle</span>
                            <x-heroicon-m-sparkles class="w-5 h-5 text-yellow-300 animate-pulse group-disabled:hidden" />
                        </div>
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        // ✅ 1. Get Database Plans from Controller
        const dbPlans = @json($plans);

        function selectNetwork(network, element = null) {
            document.getElementById('networkInput').value = network;

            // Update UI Selection state
            document.querySelectorAll('.network-card').forEach(el => {
                el.classList.remove('ring-2', 'ring-primary', 'bg-secondary');
                el.classList.add('bg-secondary/30');
            });

            if (element) {
                element.classList.remove('bg-secondary/30');
                element.classList.add('ring-2', 'ring-primary', 'bg-secondary');
            } else {
                // Auto-select logic for quick picks
                document.querySelectorAll('.network-card').forEach(el => {
                    if(el.innerText.toUpperCase().includes(network.toUpperCase())) {
                        el.classList.remove('bg-secondary/30');
                        el.classList.add('ring-2', 'ring-primary', 'bg-secondary');
                    }
                });
            }

            // Filter Plans
            const planSelect = document.getElementById('planSelect');
            planSelect.innerHTML = '<option value="" disabled selected>Select a Data Plan</option>';
            
            // Filter DB plans by Network
            const filteredPlans = dbPlans.filter(p => p.network.toUpperCase() === network.toUpperCase());

            if (filteredPlans.length > 0) {
                filteredPlans.forEach(plan => {
                    const option = document.createElement('option');
                    option.value = plan.id; // Sends DB ID
                    option.dataset.price = plan.price;
                    option.textContent = `${plan.name} - ₦${new Intl.NumberFormat().format(plan.price)}`;
                    planSelect.appendChild(option);
                });
            } else {
                planSelect.innerHTML = '<option value="" disabled>No plans available</option>';
            }
            updatePrice();
        }

        function updatePrice() {
            const planSelect = document.getElementById('planSelect');
            const priceDisplay = document.getElementById('priceDisplay');
            const submitBtn = document.getElementById('submitBtn');

            if (planSelect.selectedIndex > 0) {
                const selectedOption = planSelect.options[planSelect.selectedIndex];
                const price = selectedOption.dataset.price;
                priceDisplay.textContent = '₦' + new Intl.NumberFormat().format(price);
                submitBtn.disabled = false;
            } else {
                priceDisplay.textContent = '₦0.00';
                submitBtn.disabled = true;
            }
        }

        function fillForm(phone, network) {
            document.getElementById('phoneInput').value = phone;
            selectNetwork(network);
        }

        document.getElementById('dataForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            if (btn.disabled) { e.preventDefault(); return; }
            
            const pin = document.querySelector('input[name="transaction_pin"]').value;
            if (pin.length !== 4) {
                e.preventDefault();
                alert('Please enter a valid 4-digit PIN');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<div class="bg-gray-700/50 h-full rounded-2xl px-6 py-4 flex items-center justify-center gap-3"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="text-white">Processing...</span></div>`;
        });
    </script>
</x-app-layout>