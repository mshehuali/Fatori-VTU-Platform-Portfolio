<nav x-data="{ open: false, showBalance: localStorage.getItem('showBalance') === 'true' }"
     x-init="$watch('showBalance', val => localStorage.setItem('showBalance', val))"
     class="bg-card border-b border-border text-foreground sticky top-0 z-40">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- LEFT: Logo & Brand --}}
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center gap-2">
                    <x-application-logo class="block h-9 w-auto text-primary" />
                    {{-- Hidden on mobile to save space for balance --}}
                    <span class="font-bold text-lg hidden md:block tracking-tight text-primary">Fatori</span>
                </a>

                {{-- Desktop Links --}}
                <div class="hidden space-x-8 sm:flex sm:ml-10">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.index')">
                        Transactions
                    </x-nav-link>
                    <x-nav-link :href="route('wallet.fund.form')" :active="request()->routeIs('wallet.fund.form')">
                        Fund Wallet
                    </x-nav-link>
                </div>
            </div>

            {{-- RIGHT: Actions --}}
            <div class="flex items-center gap-2 sm:gap-3">

                {{-- ✅ WALLET PILL (NOW VISIBLE ON MOBILE) --}}
                <div class="flex items-center bg-secondary/50 px-2 py-1.5 sm:px-3 rounded-full border border-border">
                    {{-- 'Wallet:' label hidden on small mobile to fit balance --}}
                    <span class="text-xs text-muted-foreground mr-2 hidden sm:inline">Wallet:</span>
                    
                    <span class="font-bold font-mono text-sm" x-show="showBalance">
                        {{-- Safe Navigation (?->) prevents crashes --}}
                        ₦{{ number_format(auth()->user()->wallet?->current_balance ?? 0, 2) }}
                    </span>
                    
                    <span class="font-bold text-sm" x-show="!showBalance">
                        ₦ ••••••
                    </span>

                    {{-- ✅ FIX: Clean, non-glitchy Eye Icons --}}
                    <button @click="showBalance = !showBalance" class="ml-2 text-muted-foreground hover:text-primary focus:outline-none">
                        
                        {{-- Icon: Open Eye (Show when HIDDEN -> Click to View) --}}
                        <svg x-show="!showBalance" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>

                        {{-- Icon: Slashed Eye (Show when VISIBLE -> Click to Hide) --}}
                        <svg x-show="showBalance" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                {{-- Theme Toggle --}}
                <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-accent transition text-foreground">
                    <span id="themeIcon" class="text-xl">{{ config('app.theme') === 'dark' ? '🌞' : '🌙' }}</span>
                </button>

                {{-- Profile Dropdown (Desktop Only - Mobile uses Bottom Bar) --}}
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium hover:text-primary transition">
                                {{ Auth::user()->name }}
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- MOBILE BOTTOM NAVIGATION BAR --}}
<div class="sm:hidden fixed bottom-0 left-0 w-full bg-card border-t border-border pb-safe pt-2 px-6 flex justify-between items-center z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">

    {{-- Home --}}
    <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 p-2 {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-muted-foreground' }}">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="text-[10px] font-medium">Home</span>
    </a>

    {{-- History --}}
    <a href="{{ route('transactions.index') }}" class="flex flex-col items-center gap-1 p-2 {{ request()->routeIs('transactions.index') ? 'text-primary' : 'text-muted-foreground' }}">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-[10px] font-medium">History</span>
    </a>

    {{-- FUND WALLET (Central FAB) --}}
    <div class="relative -top-5">
        <a href="{{ route('wallet.fund.form') }}" class="flex items-center justify-center w-14 h-14 bg-primary text-primary-foreground rounded-full shadow-lg shadow-primary/40 transform active:scale-95 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        </a>
    </div>

    {{-- Support --}}
    <a href="https://wa.me/2348000000000" target="_blank" class="flex flex-col items-center gap-1 p-2 text-muted-foreground hover:text-primary">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-[10px] font-medium">Support</span>
    </a>

    {{-- Profile --}}
    <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-1 p-2 {{ request()->routeIs('profile.edit') ? 'text-primary' : 'text-muted-foreground' }}">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        <span class="text-[10px] font-medium">Profile</span>
    </a>
</div>