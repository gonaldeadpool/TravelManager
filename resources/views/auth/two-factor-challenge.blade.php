<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Confirm access to your account by entering the authentication code from your authenticator app, or one of your recovery codes.') }}
    </div>

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Authentication Code')" />
            <x-text-input id="code" class="mt-1 block w-full" type="text" name="code" :value="old('code')" autofocus autocomplete="one-time-code" inputmode="numeric" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="recovery_code" :value="__('Recovery Code')" />
            <x-text-input id="recovery_code" class="mt-1 block w-full" type="text" name="recovery_code" :value="old('recovery_code')" />
            <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end">
            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
