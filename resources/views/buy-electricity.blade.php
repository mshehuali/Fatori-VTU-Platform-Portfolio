{{-- resources/views/buy-electricity.blade.php --}}

<x-app-layout>
    <div class="py-6">
        <div class="max-w-md mx-auto bg-white shadow-sm rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-1">
                Buy Electricity
            </h2>

            <p class="text-xs text-slate-500 mb-4">
                Purchase prepaid or postpaid electricity tokens easily.
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

            <form method="POST" action="{{ route('electricity.purchase') }}" class="space-y-4">
                @csrf

                {{-- Disco --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Disco</label>
                    <select name="disco" class="w-full rounded-xl border-slate-300" required>
                        <option value="">Select Disco</option>
                        <option value="KEDCO"  {{ old('disco') == 'KEDCO' ? 'selected' : '' }}>KEDCO</option>
                        <option value="AEDC"   {{ old('disco') == 'AEDC' ? 'selected' : '' }}>AEDC</option>
                        <option value="IKEDC"  {{ old('disco') == 'IKEDC' ? 'selected' : '' }}>IKEDC</option>
                        <option value="EEDC"   {{ old('disco') == 'EEDC' ? 'selected' : '' }}>EEDC</option>
                        <option value="JEDC"   {{ old('disco') == 'JEDC' ? 'selected' : '' }}>JEDC</option>
                        <option value="PHEDC"  {{ old('disco') == 'PHEDC' ? 'selected' : '' }}>PHEDC</option>
                    </select>
                </div>

                {{-- Meter Type --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Meter Type</label>
                    <select name="meter_type" class="w-full rounded-xl border-slate-300" required>
                        <option value="prepaid"  {{ old('meter_type') == 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                        <option value="postpaid" {{ old('meter_type') == 'postpaid' ? 'selected' : '' }}>Postpaid</option>
                    </select>
                </div>

                {{-- Meter Number --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Meter Number</label>
                    <input
                        type="text"
                        name="meter_no"
                        value="{{ old('meter_no') }}"
                        class="w-full rounded-xl border-slate-300"
                        placeholder="Enter meter number"
                        required
                    >
                </div>

                {{-- Phone Number --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Phone Number</label>
                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        class="w-full rounded-xl border-slate-300"
                        placeholder="08012345678"
                        required
                    >
                </div>

                {{-- Amount --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Amount (₦)</label>
                    <input
                        type="number"
                        min="500"
                        name="amount"
                        value="{{ old('amount') }}"
                        class="w-full rounded-xl border-slate-300"
                        required
                    >
                </div>

                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 text-sm">
                    Buy Electricity
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
