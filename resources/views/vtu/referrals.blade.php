<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('dashboard') }}" class="group flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center group-hover:border-emerald-500 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </div>
                <span>Back</span>
            </a>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Refer & Earn</h1>
            <div class="w-8"></div>
        </div>

        {{-- Success/Error --}}
        @if (session('success') || session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="mb-6 rounded-2xl p-4 flex items-start gap-3 shadow-md border {{ session('error') ? 'bg-red-50 border-red-200 text-red-600 dark:bg-red-900/20 dark:border-red-500/30 dark:text-red-400' : 'bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-900/20 dark:border-emerald-500/30 dark:text-emerald-400' }}">
                <div class="text-sm font-medium leading-relaxed">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            
            {{-- 1. Invite Card --}}
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mb-4">
                        <x-heroicon-m-user-plus class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h2 class="text-xl font-bold mb-1 text-gray-900 dark:text-white">Invite Friends</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Earn <span class="font-bold text-emerald-600 dark:text-emerald-400">₦200</span> for every friend who funds their wallet with at least ₦500.</p>

                    {{-- Link Input --}}
                    <div class="relative mb-4 group">
                        {{-- ✅ FIXED: Explicit Light/Dark Backgrounds for readability --}}
                        <input type="text" id="refLink" value="{{ $refLink }}" readonly 
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl pl-4 pr-24 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono transition-colors"
                            onclick="this.select()">
                        
                        <button onclick="copyRef()" class="absolute right-1.5 top-1.5 bottom-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 px-4 rounded-lg text-xs font-bold transition-all shadow-sm">
                            Copy
                        </button>
                    </div>
                </div>

                {{-- ✅ FIXED: Attractive WhatsApp Button --}}
                <a href="https://wa.me/?text={{ urlencode('Join me on Fatori and get fast VTU services! Use my link: ' . $refLink) }}" target="_blank"
                   class="flex items-center justify-center gap-2 w-full bg-[#25D366] hover:bg-[#20bd5a] text-white py-3.5 rounded-xl font-bold text-sm transition-all hover:scale-[1.02] active:scale-95 shadow-lg shadow-green-500/20 group">
                    <svg class="w-5 h-5 group-hover:animate-pulse" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Share on WhatsApp
                </a>
            </div>

            {{-- 2. Withdrawal Card --}}
            <div class="bg-gradient-to-br from-emerald-900 to-emerald-950 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[250px]">
                {{-- Decorative blobs --}}
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl -mr-10 -mt-10"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-teal-500/10 rounded-full blur-2xl -ml-5 -mb-5"></div>
                
                <div class="relative z-10">
                    <p class="text-emerald-200 text-xs font-bold uppercase tracking-wider mb-2">Available to Withdraw</p>
                    <h1 class="text-5xl font-bold tracking-tight mb-2">₦{{ number_format($availableToWithdraw) }}</h1>
                    {{-- ✅ FIXED: Minimum Withdrawal Text --}}
                    <p class="text-emerald-400/80 text-xs">Minimum withdrawal: ₦1,000</p>
                </div>

                <form action="{{ route('referral.withdraw') }}" method="POST" class="relative z-10 mt-6">
                    @csrf
                    <button type="submit" 
                            @disabled($availableToWithdraw < 1000)
                            class="w-full bg-white text-emerald-900 font-bold py-3.5 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:bg-emerald-50 transition-colors shadow-lg shadow-black/10">
                        Withdraw to Wallet
                    </button>
                </form>
            </div>
        </div>

        {{-- 3. Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 rounded-2xl shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Friends</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalFriends }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 rounded-2xl shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Pending</p>
                <p class="text-2xl font-bold text-orange-500">{{ $pendingCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 rounded-2xl shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Earned</p>
                <p class="text-2xl font-bold text-green-500">₦{{ number_format($totalWithdrawn + $availableToWithdraw) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 rounded-2xl shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Withdrawn</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">₦{{ number_format($totalWithdrawn) }}</p>
            </div>
        </div>

        {{-- 4. Referral List --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <h3 class="font-bold text-gray-900 dark:text-white">Referral History</h3>
            </div>
            
            @if($referrals->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-heroicon-o-users class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">You haven't referred anyone yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-4">Friend</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Bonus</th>
                                <th class="px-6 py-4 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($referrals as $ref)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $ref->friend_name ?? 'User' }}</td>
                                    <td class="px-6 py-4">
                                        @if($ref->status === 'paid')
                                            <span class="bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400 px-2.5 py-1 rounded-full text-xs font-bold border border-green-200 dark:border-green-500/20">Paid</span>
                                        @elseif($ref->status === 'rewarded')
                                            <span class="bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 px-2.5 py-1 rounded-full text-xs font-bold border border-blue-200 dark:border-blue-500/20">Ready</span>
                                        @else
                                            <span class="bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400 px-2.5 py-1 rounded-full text-xs font-bold border border-orange-200 dark:border-orange-500/20">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono text-gray-700 dark:text-gray-300">₦{{ number_format($ref->bonus_amount) }}</td>
                                    <td class="px-6 py-4 text-right text-gray-500 dark:text-gray-400 text-xs">{{ $ref->created_at->format('M d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <script>
        function copyRef() {
            const copyText = document.getElementById("refLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            // Visual feedback
            const btn = event.target;
            const originalText = btn.innerText;
            btn.innerText = "Copied!";
            btn.classList.add('bg-emerald-600', 'text-white', 'border-transparent');
            btn.classList.remove('bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-200');
            
            setTimeout(() => {
                btn.innerText = originalText;
                btn.classList.remove('bg-emerald-600', 'text-white', 'border-transparent');
                btn.classList.add('bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-200');
            }, 2000);
        }
    </script>
</x-app-layout>