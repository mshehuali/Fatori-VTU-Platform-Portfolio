@php
    $services = [
        ['icon' => 'phone', 'title' => 'Airtime', 'route' => 'airtime.index', 'color' => 'text-blue-500', 'bg' => 'bg-blue-500/10'],
        ['icon' => 'wifi', 'title' => 'Data', 'route' => 'data.index', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-500/10'],
        ['icon' => 'tv', 'title' => 'Cable TV', 'route' => 'cable.index', 'color' => 'text-orange-500', 'bg' => 'bg-orange-500/10'],
        ['icon' => 'bolt', 'title' => 'Electricity', 'route' => 'electricity.index', 'color' => 'text-yellow-500', 'bg' => 'bg-yellow-500/10'],
        ['icon' => 'academic-cap', 'title' => 'Exams', 'route' => 'education.index', 'color' => 'text-purple-500', 'bg' => 'bg-purple-500/10'],
        
        // REPLACED 'MORE' WITH 'REFERRALS'
        ['icon' => 'user-group', 'title' => 'Referrals', 'route' => 'referral.index', 'color' => 'text-pink-500', 'bg' => 'bg-pink-500/10'],
    ];
@endphp

<div class="py-2">
    {{-- Grid: 3 columns on mobile, 6 on desktop --}}
    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-x-4 gap-y-6">
        @foreach ($services as $s)
            <a href="{{ Route::has($s['route']) ? route($s['route']) : '#' }}" 
               class="group flex flex-col items-center gap-2 transition-transform active:scale-95">
                
                {{-- Icon Bubble --}}
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm border border-white/5 {{ $s['bg'] }} group-hover:shadow-md transition-all">
                    <x-dynamic-component 
                        :component="'heroicon-o-' . $s['icon']" 
                        class="w-7 h-7 {{ $s['color'] }}" 
                    />
                </div>
                
                {{-- Title --}}
                <span class="text-xs font-medium text-center text-foreground truncate w-full">
                    {{ $s['title'] }}
                </span>
            </a>
        @endforeach
    </div>
</div>