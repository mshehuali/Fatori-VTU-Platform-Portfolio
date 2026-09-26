<x-app-layout>
    <div class="max-w-xl mx-auto py-8 px-4">

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Refer & Earn</h1>
        <p class="text-slate-600 text-sm mb-4">
            Share your referral link and earn when your friends use the VTU platform.
        </p>

        <div class="bg-white rounded-2xl shadow p-4 border border-slate-200">
            <p class="text-xs text-slate-500 mb-1">Your Referral Link</p>

            <div class="flex items-center gap-2">
                <input
                    type="text"
                    value="{{ $referralLink }}"
                    readonly
                    class="flex-1 rounded-xl border-slate-300 text-sm"
                    id="refLink"
                >
                <button
                    onclick="navigator.clipboard.writeText(document.getElementById('refLink').value);"
                    class="px-3 py-2 bg-emerald-600 text-white text-xs rounded-xl hover:bg-emerald-700">
                    Copy
                </button>
            </div>
        </div>

    </div>
</x-app-layout>
