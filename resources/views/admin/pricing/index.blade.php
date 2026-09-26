@extends('layouts.app')

@section('title', 'Price Control Center')

@section('content')
<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    
    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Price Control Center</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Manage your selling prices, discounts, and profit margins.</p>
        </div>
        
        {{-- Quick Stats --}}
        <div class="bg-indigo-600 px-4 py-2 rounded-xl text-white text-sm font-bold shadow-lg">
            {{ $plans->flatten()->count() }} Data Plans &bull; {{ $airtime->count() }} Networks
        </div>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-xl font-bold border border-green-200 dark:border-green-800 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- 1. AIRTIME CONTROLS SECTION (NEW) --}}
    {{-- ========================================== --}}
    <div class="mb-12 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-50 dark:bg-indigo-900/20 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Airtime Discounts
            </h2>
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-900 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-800">
                Set % Discount for Users
            </span>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($airtime as $net)
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 relative group hover:border-indigo-500 transition">
                    
                    {{-- Network Header --}}
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-bold text-sm text-gray-700 dark:text-gray-300">{{ $net->network }}</span>
                        <div class="w-2 h-2 rounded-full {{ $net->is_active ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    </div>

                    {{-- Update Form --}}
                    <form action="{{ route('admin.pricing.airtime.update', $net->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="flex items-center gap-2 mb-3">
                            <div class="relative w-full">
                                <input type="number" step="0.01" name="discount" value="{{ $net->discount_percent }}" 
                                       class="w-full pl-3 pr-8 py-2 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-bold focus:ring-indigo-500 focus:border-indigo-500">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-bold">%</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            {{-- Active Toggle --}}
                            <label class="relative inline-flex items-center cursor-pointer" title="Toggle Status">
                                <input type="checkbox" name="status" value="1" class="sr-only peer" {{ $net->is_active ? 'checked' : '' }}>
                                <div class="w-7 h-4 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>

                            <button type="submit" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg transition shadow-sm">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. DATA PLANS SECTION (EXISTING) --}}
    {{-- ========================================== --}}
    <div class="grid gap-8">
        @foreach($plans as $network => $networkPlans)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    {{-- Network Icon Placeholder --}}
                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white text-xs
                        {{ $network == 'MTN' ? 'bg-yellow-400' : ($network == 'GLO' ? 'bg-green-500' : ($network == 'AIRTEL' ? 'bg-red-500' : 'bg-green-600')) }}">
                        {{ substr($network, 0, 1) }}
                    </span>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white uppercase">{{ $network }} Data</h2>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase font-bold text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Plan Name</th>
                            <th class="px-6 py-3">Cost Price</th>
                            <th class="px-6 py-3">Selling Price (₦)</th>
                            <th class="px-6 py-3">Profit</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($networkPlans as $plan)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            
                            {{-- Plan Name --}}
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $plan->name }}
                                <span class="block text-xs text-gray-400 font-mono">{{ $plan->plan_id }}</span>
                            </td>

                            {{-- Cost Price (Read Only) --}}
                            <td class="px-6 py-4">
                                ₦{{ number_format($plan->cost_price, 2) }}
                            </td>

                            {{-- Form Starts Here --}}
                            <form action="{{ route('admin.pricing.update', $plan->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                {{-- Editable Selling Price --}}
                                <td class="px-6 py-4">
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₦</span>
                                        <input type="number" step="0.01" name="price" value="{{ $plan->price }}" 
                                               class="w-32 pl-7 pr-3 py-1.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold shadow-sm">
                                    </div>
                                </td>

                                {{-- Live Profit Calc --}}
                                <td class="px-6 py-4">
                                    @php $profit = $plan->price - $plan->cost_price; @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $profit > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $profit > 0 ? '+' : '' }}₦{{ number_format($profit, 2) }}
                                    </span>
                                </td>

                                {{-- Toggle Status --}}
                                <td class="px-6 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="status" value="1" class="sr-only peer" {{ $plan->is_active ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                    </label>
                                </td>

                                {{-- Save Button --}}
                                <td class="px-6 py-4 text-right">
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold text-sm">
                                        Save
                                    </button>
                                </td>
                            </form>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection