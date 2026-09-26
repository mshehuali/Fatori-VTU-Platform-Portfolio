<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Transaction PIN') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Set a 4-digit PIN to secure your wallet transactions.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.pin.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        {{-- 1. Security Check: Password --}}
        <div>
            <x-input-label for="current_password_pin" :value="__('Current Password')" />
            {{-- ✅ FIX: Removed hardcoded bg-[#0B1120]. Now uses standard colors that adapt to theme. --}}
            <x-text-input id="current_password_pin" name="current_password" type="password" 
                class="mt-1 block w-full" 
                autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        {{-- 2. New PIN --}}
        <div>
            <x-input-label for="pin" :value="__('New 4-Digit PIN')" />
            <x-text-input id="pin" name="pin" type="password" maxlength="4" inputmode="numeric" 
                class="mt-1 block w-full" 
                placeholder="••••" />
            <x-input-error :messages="$errors->get('pin')" class="mt-2" />
        </div>

        {{-- 3. Confirm PIN --}}
        <div>
            <x-input-label for="pin_confirmation" :value="__('Confirm PIN')" />
            <x-text-input id="pin_confirmation" name="pin_confirmation" type="password" maxlength="4" inputmode="numeric" 
                class="mt-1 block w-full" 
                placeholder="••••" />
            <x-input-error :messages="$errors->get('pin_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save PIN') }}</x-primary-button>

            @if (session('status') === 'pin-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-green-600 dark:text-green-400">
                    {{ __('PIN Updated Successfully.') }}
                </p>
            @endif
        </div>
    </form>
</section>