@php
    // We map the Controller variables to the UI here
    $stats = [
        [
            'icon' => 'chart-bar', 
            'value' => number_format($totalTransactions ?? 0), 
            'label' => 'Total Transactions', 
            'color' => 'text-blue-500', 
            'bg' => 'bg-blue-500/10'
        ],
        [
            'icon' => 'banknotes', 
            'value' => '₦' . number_format($todaySpend ?? 0, 2), // Fixed variable name
            'label' => 'Spent Today', 
            'color' => 'text-orange-500', 
            'bg' => 'bg-orange-500/10'
        ],
        [
            'icon' => 'user-group', 
            'value' => '₦' . number_format($referralEarnings ?? 0, 2), // Fixed variable name
            'label' => 'Referral Earnings', 
            'color' => 'text-purple-500', 
            'bg' => 'bg-purple-500/10'
        ],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach($stats as $s)
        <div class="group relative overflow-hidden rounded-2xl bg-card border border-border p-5 hover:border-emerald-500/30 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            
            <div class="flex items-center gap-4">
                {{-- Icon Bubble --}}
                <div class="h-12 w-12 rounded-xl flex items-center justify-center shrink-0 {{ $s['bg'] }}">
                    <x-dynamic-component 
                        :component="'heroicon-o-'.$s['icon']" 
                        class="h-6 w-6 {{ $s['color'] }}" 
                    />
                </div>

                {{-- Text Content --}}
                <div>
                    <p class="text-sm font-medium text-muted-foreground">
                        {{ $s['label'] }}
                    </p>
                    <p class="text-2xl font-bold tracking-tight text-foreground">
                        {{ $s['value'] }}
                    </p>
                </div>
            </div>

            {{-- Decor: Subtle faint glow in the background --}}
            <div class="absolute -right-6 -bottom-6 h-24 w-24 rounded-full opacity-10 blur-2xl {{ str_replace('text-', 'bg-', $s['color']) }}"></div>
        </div>
    @endforeach
</div>