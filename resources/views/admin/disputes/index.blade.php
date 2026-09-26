@extends('layouts.app')

@section('title', 'Disputed Transactions')

@section('content')
<div class="container mx-auto py-12 px-4 max-w-6xl">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span>⚠️</span> Disputed Transactions
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Review reported issues and take action.
            </p>
        </div>
    </div>

    @if($disputed->count())
        {{-- Card Container --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    {{-- Table Head --}}
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase font-bold text-gray-500 dark:text-gray-400 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Reference</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                            <th class="px-6 py-4">Reason</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    {{-- Table Body --}}
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($disputed as $tx)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            {{-- Reference --}}
                            <td class="px-6 py-4 font-mono text-xs font-medium text-gray-900 dark:text-white">
                                {{ $tx->reference }}
                            </td>

                            {{-- User Info --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 dark:text-white">
                                        {{ optional($tx->wallet->user)->name ?? 'Unknown User' }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ optional($tx->wallet->user)->email }}
                                    </span>
                                </div>
                            </td>

                            {{-- Amount --}}
                            <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                ₦{{ number_format($tx->amount, 2) }}
                            </td>

                            {{-- Reason Badge --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                    {{ $tx->meta['dispute_reason'] ?? 'General Dispute' }}
                                </span>
                            </td>

                            {{-- Date --}}
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $tx->created_at->format('M d, Y') }}<br>
                                <span class="opacity-70">{{ $tx->created_at->format('h:i A') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right space-x-2">
                                {{-- View Receipt --}}
                                <a href="{{ route('transactions.receipt', $tx->reference) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 transition">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    View
                                </a>

                                {{-- Resolve (Placeholder for logic) --}}
                                @if(Route::has('txn.rollback'))
                                <form action="{{ route('txn.rollback', $tx->reference) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to rollback (refund) this transaction?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-gray-900 hover:bg-black dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 transition">
                                        Resolve
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $disputed->links() }}
        </div>

    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-20 bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">No Disputes</h3>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm text-center">
                Everything looks good! There are no disputed transactions requiring your attention right now.
            </p>
        </div>
    @endif

</div>
@endsection