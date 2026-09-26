{{-- resources/views/buy-cable.blade.php --}}

<x-app-layout>
    <div class="py-6">
        <div class="max-w-md mx-auto bg-white shadow-sm rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-1">
                Pay Cable TV
            </h2>

            <p class="text-xs text-slate-500 mb-4">
                Renew your DSTV, GOTV or StarTimes subscription.
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

            <form method="POST" action="{{ route('cable.purchase') }}" class="space-y-4">
                @csrf

                {{-- Provider --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Provider</label>
                    <select name="provider" class="w-full rounded-xl border-slate-300" required>
                        <option value="DSTV"      {{ old('provider') == 'DSTV' ? 'selected' : '' }}>DSTV</option>
                        <option value="GOTV"      {{ old('provider') == 'GOTV' ? 'selected' : '' }}>GOTV</option>
                        <option value="Startimes" {{ old('provider') == 'Startimes' ? 'selected' : '' }}>StarTimes</option>
                    </select>
                </div>

                {{-- Smartcard / IUC Number --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Smartcard / IUC Number</label>
                    <input
                        type="text"
                        name="smartcard"
                        value="{{ old('smartcard') }}"
                        class="w-full rounded-xl border-slate-300"
                        placeholder="12345678901"
                        required
                    >
                </div>

                {{-- Package / Bouquet (dropdown with prices) --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Package / Bouquet</label>
                    <select
                        id="package-select"
                        name="package"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                        <option value="">Select package</option>

                        {{-- You can change these names/prices to match your real VTU plans --}}
                        <option value="basic" {{ old('package') == 'basic' ? 'selected' : '' }}>
                            Basic — ₦4,500
                        </option>
                        <option value="compact" {{ old('package') == 'compact' ? 'selected' : '' }}>
                            Compact — ₦7,000
                        </option>
                        <option value="premium" {{ old('package') == 'premium' ? 'selected' : '' }}>
                            Premium — ₦12,000
                        </option>
                    </select>
                </div>

                {{-- Amount (auto-filled after package selection) --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Amount (₦)</label>
                    <input
                        type="number"
                        min="1000"
                        id="amount-input"
                        name="amount"
                        value="{{ old('amount') }}"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                    <p class="mt-1 text-[11px] text-slate-400">
                        Amount is auto-filled from the selected package (you can still adjust if needed).
                    </p>
                </div>

                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 text-sm">
                    Pay Cable Subscription
                </button>
            </form>
        </div>
    </div>

    {{-- Auto-fill amount when package changes --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const packageSelect = document.getElementById('package-select');
            const amountInput   = document.getElementById('amount-input');

            // Map package code -> amount
            const packagePrices = {
                basic:   4500,
                compact: 7000,
                premium: 12000,
            };

            function updateAmount() {
                const pkg = packageSelect.value;
                if (pkg && packagePrices[pkg]) {
                    amountInput.value = packagePrices[pkg];
                }
            }

            // Update on change
            packageSelect.addEventListener('change', updateAmount);

            // If page has old selected value from validation, set amount on load
            updateAmount();
        });
    </script>
</x-app-layout>
