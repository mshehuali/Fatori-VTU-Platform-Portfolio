<x-guest-layout>
    <div class="bg-[#161b26]/80 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-500"></div>

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-2">Create Account 🚀</h2>
            <p class="text-sm text-gray-400">Join the future of VTU services</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            {{-- Referral Badge --}}
            @if(request()->query('ref'))
                <input type="hidden" name="referral_code" value="{{ request()->query('ref') }}">
                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-3 text-center mb-6">
                    <p class="text-xs text-emerald-400 font-medium">
                        Referral applied: <span class="font-mono font-bold text-white ml-1">{{ request()->query('ref') }}</span>
                    </p>
                </div>
            @endif

            {{-- Name --}}
            <div class="space-y-2">
                <label for="name" class="text-xs font-bold text-gray-500 uppercase tracking-widest">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                    class="w-full bg-[#0f111a] border border-gray-700 text-white text-sm rounded-xl px-4 py-3.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder:text-gray-700"
                    placeholder="John Doe">
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            {{-- Email --}}
            <div class="space-y-2">
                <label for="email" class="text-xs font-bold text-gray-500 uppercase tracking-widest">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                    class="w-full bg-[#0f111a] border border-gray-700 text-white text-sm rounded-xl px-4 py-3.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder:text-gray-700"
                    placeholder="you@example.com">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            {{-- Phone --}}
            <div class="space-y-2">
                <label for="phone" class="text-xs font-bold text-gray-500 uppercase tracking-widest">Phone Number</label>
                <input id="phone" type="tel" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel"
                    class="w-full bg-[#0f111a] border border-gray-700 text-white text-sm rounded-xl px-4 py-3.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder:text-gray-700"
                    placeholder="08012345678">
                <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
            </div>

            {{-- Password Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="password" class="text-xs font-bold text-gray-500 uppercase tracking-widest">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="w-full bg-[#0f111a] border border-gray-700 text-white text-sm rounded-xl px-4 py-3.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder:text-gray-700"
                        placeholder="••••••">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-xs font-bold text-gray-500 uppercase tracking-widest">Confirm</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full bg-[#0f111a] border border-gray-700 text-white text-sm rounded-xl px-4 py-3.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder:text-gray-700"
                        placeholder="••••••">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-600/20 transition-all transform active:scale-[0.98] mt-2">
                Create Account
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold hover:underline transition">Sign in instead</a>
            </p>
        </div>
    </div>
</x-guest-layout>