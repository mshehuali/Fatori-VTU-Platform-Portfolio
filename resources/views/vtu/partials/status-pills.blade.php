<div class="flex flex-wrap items-center gap-3">

    <div class="flex items-center gap-2 bg-pill text-pill px-4 py-2
                rounded-full border border-pill">
        <x-heroicon-o-check-circle class="w-4 h-4" />
        <span class="text-sm font-medium">Platform Online</span>
        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
    </div>

    <div class="flex items-center gap-2 bg-pill text-pill px-4 py-2
                rounded-full border border-pill">
        <x-heroicon-o-calendar class="w-4 h-4" />
        <span class="text-sm font-medium">
            Joined {{ auth()->user()->created_at->format('M Y') }}
        </span>
    </div>

    <div class="flex items-center gap-2 bg-pill text-pill px-4 py-2
                rounded-full border border-pill">
        <x-heroicon-o-gift class="w-4 h-4" />
        <span class="text-sm font-medium">Referral Active</span>
    </div>

    <button
    onclick="copyWithFeedback(this, '{{ url('/register?ref=' . auth()->user()->referral_code) }}')"
    class="flex items-center gap-2 bg-pill text-pill px-4 py-2
           rounded-full border border-pill transition"
    >
        <x-heroicon-o-clipboard class="w-4 h-4" />
        <span class="text-sm font-medium">Copy Link</span>
    </button>


</div>
