{{-- resources/views/buy-data.blade.php --}}

<x-app-layout>
    <div class="py-6">
        <div class="max-w-md mx-auto bg-white shadow-sm rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-1">
                Buy Data
            </h2>

            <p class="text-xs text-slate-500 mb-4">
                Cheap data bundles for all networks.
            </p>

            {{-- Success message --}}
            @if (session('success'))
                <div class="mb-3 rounded-lg bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-800">
                    <ul class="list-disc ms-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('data.purchase') }}" class="space-y-4">
                @csrf

                {{-- Network --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold text-slate-600">
                            Network
                        </label>
                        <div id="networkBadge" class="text-[11px] px-2 py-0.5 rounded-full hidden">
                            {{-- JS will fill this --}}
                        </div>
                    </div>

                    <select
                        name="network"
                        id="networkSelect"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        required
                    >
                        <option value="" disabled {{ old('network') ? '' : 'selected' }}>Select network</option>
                        @foreach ($dataPlans as $networkName => $plans)
                            <option
                                value="{{ $networkName }}"
                                {{ old('network') == $networkName ? 'selected' : '' }}
                            >
                                {{ $networkName }}
                            </option>
                        @endforeach
                    </select>

                    @error('network')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone Number --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Phone Number</label>

                    <input
                        type="text"
                        name="phone"
                        id="phoneInput"
                        value="{{ old('phone') }}"
                        maxlength="14"
                        pattern="^(0\d{10}|234\d{10}|\+234\d{10})$"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        placeholder="08012345678"
                        required
                    >

                    <p class="mt-1 text-[10px] text-slate-400">
                        Type 8012345678 → it becomes 08012345678 automatically.
                        Accepted: 08012345678 · 2348012345678 · +2348012345678
                    </p>

                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Data Plan (Plan · Validity · Price) --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">
                        Data Plan (Plan · Validity · Price)
                    </label>

                    <select
                        name="plan"
                        id="planSelect"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        required
                    >
                        <option value="" disabled {{ old('plan') ? '' : 'selected' }}>Select plan</option>

                        @foreach ($dataPlans as $networkName => $plans)
                            <optgroup label="{{ $networkName }}">
                                @foreach ($plans as $plan)
                                    <option
                                        value="{{ $plan['code'] }}"
                                        data-amount="{{ $plan['amount'] }}"
                                        data-network="{{ $plan['network'] }}"
                                        @if(old('plan') === $plan['code']) selected @endif
                                    >
                                        {{ $plan['name'] }} · {{ $plan['validity'] }} ·
                                        ₦{{ number_format($plan['amount'], 0, ',', ',') }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    @error('plan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Amount (auto-filled from plan, read-only) --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Amount (₦)</label>

                    <input
                        type="number"
                        name="amount"
                        id="amountInput"
                        value="{{ old('amount') }}"
                        class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50"
                        readonly
                        required
                    >

                    <p class="mt-1 text-[10px] text-slate-400">
                        Amount is automatically set based on selected data plan.
                    </p>

                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 text-sm">
                    Buy Data
                </button>
            </form>
        </div>
    </div>

    {{-- JS: phone auto-format, network detection, dynamic plans --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInput    = document.getElementById('phoneInput');
            const networkSelect = document.getElementById('networkSelect');
            const planSelect    = document.getElementById('planSelect');
            const amountInput   = document.getElementById('amountInput');
            const networkBadge  = document.getElementById('networkBadge');

            if (!phoneInput || !networkSelect || !planSelect || !amountInput) return;

            // Simple network prefix map (you can expand later)
            const prefixMap = {
                'MTN':     ['0803','0806','0703','0706','0810','0813','0814','0816','0903','0906','0913','0916'],
                'Airtel':  ['0802','0808','0708','0812','0902','0907','0901','0912'],
                'Glo':     ['0805','0807','0705','0811','0815','0905','0915'],
                '9mobile': ['0809','0817','0818','0909','0908'],
            };

            function detectNetworkFromPhone(phone) {
                const digits = phone.replace(/\D/g, '');

                let local;
                if (digits.startsWith('234') && digits.length >= 13) {
                    local = '0' + digits.slice(3, 6); // e.g. 234803 → 0803
                } else if (digits.length >= 4) {
                    local = digits.slice(0, 4);
                    if (!local.startsWith('0') && digits.length === 10) {
                        local = '0' + local.slice(0, 3);
                    }
                } else {
                    return null;
                }

                for (const [network, prefixes] of Object.entries(prefixMap)) {
                    if (prefixes.includes(local)) {
                        return network;
                    }
                }

                return null;
            }

            function updateNetworkBadge(network) {
                if (!network) {
                    networkBadge.classList.add('hidden');
                    networkBadge.textContent = '';
                    networkBadge.className = 'text-[11px] px-2 py-0.5 rounded-full hidden';
                    return;
                }

                let emoji = '📶';
                if (network === 'MTN') emoji = '💛';
                if (network === 'Airtel') emoji = '🟥';
                if (network === 'Glo') emoji = '🟢';
                if (network === '9mobile') emoji = '🟩';

                networkBadge.textContent = emoji + ' ' + network;
                networkBadge.className =
                    'text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200';
            }

            // Auto-format phone (e.g. 8012345678 → 08012345678)
            phoneInput.addEventListener('blur', function () {
                let digits = phoneInput.value.replace(/\D/g, '');

                if (digits.length === 10 && !digits.startsWith('0')) {
                    digits = '0' + digits;
                }

                if (digits.length === 11 && digits.startsWith('0')) {
                    phoneInput.value = digits;
                } else if (digits.startsWith('234') && digits.length >= 13) {
                    phoneInput.value = '+' + digits;
                } else {
                    // leave as is, browser + backend validation will handle
                    phoneInput.value = phoneInput.value;
                }

                const detected = detectNetworkFromPhone(phoneInput.value);
                if (detected) {
                    networkSelect.value = detected;
                    updateNetworkBadge(detected);
                    filterPlansByNetwork(detected);
                }
            });

            // Filter plans by selected network
            function filterPlansByNetwork(network) {
                for (const option of planSelect.options) {
                    if (!option.value) continue; // skip placeholder
                    const optNetwork = option.getAttribute('data-network');
                    if (!optNetwork || !network) {
                        option.hidden = false;
                    } else {
                        option.hidden = (optNetwork.toLowerCase() !== network.toLowerCase());
                    }
                }

                // If current selected plan doesn't match network, reset
                const selectedOption = planSelect.options[planSelect.selectedIndex];
                if (selectedOption) {
                    const optNetwork = selectedOption.getAttribute('data-network');
                    if (optNetwork && optNetwork.toLowerCase() !== network.toLowerCase()) {
                        planSelect.value = '';
                        amountInput.value = '';
                    }
                }
            }

            // Network change → filter plans + update badge
            networkSelect.addEventListener('change', function () {
                const network = networkSelect.value || null;
                updateNetworkBadge(network);
                filterPlansByNetwork(network);
            });

            // Plan change → set amount + auto-select network of that plan
            function updateAmountFromPlan() {
                const selected = planSelect.options[planSelect.selectedIndex];
                if (!selected) {
                    amountInput.value = '';
                    return;
                }

                const amount   = selected.getAttribute('data-amount');
                const network  = selected.getAttribute('data-network');

                if (amount) {
                    amountInput.value = amount;
                } else {
                    amountInput.value = '';
                }

                if (network) {
                    networkSelect.value = network;
                    updateNetworkBadge(network);
                    filterPlansByNetwork(network);
                }
            }

            planSelect.addEventListener('change', updateAmountFromPlan);

            // Initial run (in case of old() after validation failure)
            updateAmountFromPlan();
            if (networkSelect.value) {
                updateNetworkBadge(networkSelect.value);
                filterPlansByNetwork(networkSelect.value);
            }
        });
    </script>
</x-app-layout>
