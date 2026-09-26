@php
    if (!function_exists('naira')) {
        function naira($n) {
            return '₦' . number_format($n, 2);
        }
    }
@endphp

<section class="mt-8">
    <div class="flex items-center justify-between mb-4 px-1">
        <h2 class="text-lg font-semibold text-foreground">Recent Activity</h2>
        <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-emerald-500 hover:text-emerald-400 transition">View All</a>
    </div>

    <div class="bg-card rounded-2xl border border-border overflow-hidden shadow-sm">
        @forelse($recentTransactions ?? [] as $tx)
            @php
                // Logic: Determine if money left the wallet (Debit)
                $isDebit = strtolower($tx->type ?? '') === 'debit' || strtolower($tx->direction ?? '') === 'debit';
                
                // Status normalization
                $status = strtolower($tx->status ?? 'pending');
                $isSuccess = in_array($status, ['success', 'successful', 'paid', 'approved', '1']);
                $isFailed = in_array($status, ['failed', 'reversed', 'cancelled', '0']);
            @endphp

            <div class="flex items-center justify-between p-4 border-b border-border last:border-0 hover:bg-white/5 transition duration-200">
                <div class="flex items-center gap-4">
                    
                    {{-- DYNAMIC ICON --}}
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                        {{ $isSuccess ? ($isDebit ? 'bg-orange-500/10 text-orange-500' : 'bg-emerald-500/10 text-emerald-500') : ($isFailed ? 'bg-red-500/10 text-red-500' : 'bg-yellow-500/10 text-yellow-500') }}">
                        
                        @if($isFailed)
                            <x-heroicon-m-exclamation-triangle class="w-5 h-5" />
                        @elseif(!$isSuccess)
                            <x-heroicon-m-clock class="w-5 h-5 animate-pulse" />
                        @elseif(Str::contains(strtolower($tx->description ?? ''), ['data', 'bundle', 'internet']))
                            <x-heroicon-m-wifi class="w-5 h-5" />
                        @elseif(Str::contains(strtolower($tx->description ?? ''), ['airtime', 'recharge']))
                            <x-heroicon-m-phone class="w-5 h-5" />
                        @elseif($isDebit)
                            <x-heroicon-m-arrow-up-right class="w-5 h-5" /> 
                        @else
                            <x-heroicon-m-arrow-down-left class="w-5 h-5" /> 
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="font-medium text-sm text-foreground truncate">
                            {{ Str::limit($tx->description ?? $tx->reference, 25) }}
                        </p>
                        <p class="text-[11px] text-muted-foreground">
                            {{ $tx->created_at->format('M d, h:i A') }}
                        </p>
                    </div>
                </div>

                <div class="text-right pl-2">
                    {{-- FIX APPLIED HERE: If Debit, use text-red-500. If Credit, use text-emerald-500 --}}
                    <p class="font-bold text-sm font-mono {{ $isDebit ? 'text-red-500' : 'text-emerald-500' }}">
                        {{ $isDebit ? '-' : '+' }}₦{{ number_format($tx->amount) }}
                    </p>
                    <p class="text-[10px] uppercase font-bold tracking-wider {{ $isSuccess ? 'text-emerald-500' : ($isFailed ? 'text-red-500' : 'text-yellow-500') }}">
                        {{ $status }}
                    </p>
                </div>
            </div>
        @empty
            <div class="py-12 flex flex-col items-center justify-center text-muted-foreground">
                <div class="w-16 h-16 bg-muted/20 rounded-full flex items-center justify-center mb-3">
                    <x-heroicon-o-document-text class="w-8 h-8 opacity-50" />
                </div>
                <p class="text-sm">No recent transactions</p>
            </div>
        @endforelse
    </div>
</section>