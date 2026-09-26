<x-guest-layout>
    <div class="bg-[#161b26]/80 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden group">
        
        {{-- Top Gradient Border --}}
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-500"></div>

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-2">Welcome to Fatori! 👋</h2>
            <p class="text-sm text-gray-400">Please sign-in to your account</p>
        </div>

        {{-- Session Status --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            {{-- FIXED: Email/Phone Field - name="email" --}}
            <div class="space-y-2">
                <label for="login" class="text-xs font-bold text-gray-500 uppercase tracking-widest">Email or Phone</label>
                <div class="relative">
                    <input id="login" 
                           type="text" 
                           name="email"  {{-- ✅ FIXED: name="email" --}}
                           value="{{ old('login') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           class="w-full bg-[#0f111a] border border-gray-700 text-white text-sm rounded-xl px-4 py-3.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder:text-gray-700"
                           placeholder="Enter your email or phone">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1" />  {{-- ✅ FIXED: email --}}
            </div>

            {{-- Password Field --}}
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <label for="password" class="text-xs font-bold text-gray-500 uppercase tracking-widest">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-emerald-500 hover:text-emerald-400 transition font-medium">Forgot Password?</a>
                    @endif
                </div>
                
                <div class="relative">
                    <input id="password" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           class="w-full bg-[#0f111a] border border-gray-700 text-white text-sm rounded-xl px-4 py-3.5 pr-12 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder:text-gray-700"
                           placeholder="••••••••">
                    
                    <button type="button" id="togglePass" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-white transition cursor-pointer">
                        <svg class="w-5 h-5 eye-off" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                        <svg class="w-5 h-5 eye-on hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            {{-- Remember Me --}}
            <div class="block">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <input id="remember_me" type="checkbox" name="remember" class="rounded bg-[#0f111a] border-gray-600 text-emerald-600 shadow-sm focus:ring-emerald-500 focus:ring-offset-gray-900">
                    <span class="ml-3 text-sm text-gray-400 group-hover:text-gray-300 transition">Remember Me</span>
                </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-600/20 transition-all transform active:scale-[0.98]">
                Sign In
            </button>
        </form>

        {{-- Footer --}}
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                New on our platform? 
                <a href="{{ route('register') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold hover:underline transition">Create an account</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('togglePass')?.addEventListener('click', function() {
            const input = document.getElementById('password');
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            this.querySelector('.eye-off').classList.toggle('hidden');
            this.querySelector('.eye-on').classList.toggle('hidden');
        });
    </script>
</x-guest-layout>
