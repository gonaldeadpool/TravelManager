<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add an extra security step by using an authenticator app when you log in.') }}
        </p>
    </header>

    @if (! $twoFactorEnabled && ! $twoFactorPendingConfirmation)
        <form method="post" action="{{ route('two-factor.enable') }}" class="mt-6 space-y-6">
            @csrf

            <div>
                <x-input-label for="enable_two_factor_current_password" :value="__('Current Password')" />
                <x-text-input id="enable_two_factor_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->enableTwoFactor->get('current_password')" class="mt-2" />
            </div>

            <x-primary-button>{{ __('Enable Two-Factor Authentication') }}</x-primary-button>
        </form>
    @endif

    @if ($twoFactorPendingConfirmation)
        <div class="mt-6 space-y-4">
            <p class="text-sm text-gray-600">
                {{ __('Scan this setup key in your authenticator app, then enter the generated code to confirm.') }}
            </p>

            @if (! empty($twoFactorQrSvg))
                <div>
                    <x-input-label :value="__('QR Code')" />
                    <div class="mt-2 inline-block rounded-md border border-gray-200 bg-white p-3">
                        {!! $twoFactorQrSvg !!}
                    </div>
                </div>
            @endif

            <div>
                <x-input-label for="two_factor_secret" :value="__('Setup Key')" />
                <x-text-input id="two_factor_secret" type="text" class="mt-1 block w-full" :value="$twoFactorSecret" readonly />
            </div>

            <div>
                <x-input-label for="two_factor_uri" :value="__('Provisioning URI')" />
                <textarea id="two_factor_uri" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $twoFactorProvisioningUri }}</textarea>
            </div>

            <form method="post" action="{{ route('two-factor.confirm') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="two_factor_code" :value="__('Authenticator Code')" />
                    <x-text-input id="two_factor_code" name="code" type="text" class="mt-1 block w-full" inputmode="numeric" autocomplete="one-time-code" />
                    <x-input-error :messages="$errors->confirmTwoFactor->get('code')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Confirm Two-Factor Authentication') }}</x-primary-button>
            </form>
        </div>
    @endif

    @if ($twoFactorEnabled)
        <div class="mt-6 space-y-6">
            <p class="text-sm text-gray-600">
                {{ __('Two-factor authentication is active on your account.') }}
            </p>

            <div>
                <h3 class="text-sm font-medium text-gray-900">{{ __('Recovery Codes') }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Store these codes safely. Each code can be used once if you lose access to your authenticator app.') }}
                </p>

                <ul class="mt-3 grid grid-cols-1 gap-2 rounded-md bg-gray-100 p-4 text-sm text-gray-800 sm:grid-cols-2">
                    @foreach ($twoFactorRecoveryCodes as $code)
                        <li>{{ $code }}</li>
                    @endforeach
                </ul>
            </div>

            <form method="post" action="{{ route('two-factor.recovery-codes') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="recovery_codes_current_password" :value="__('Current Password')" />
                    <x-text-input id="recovery_codes_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                    <x-input-error :messages="$errors->recoveryCodes->get('current_password')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Regenerate Recovery Codes') }}</x-primary-button>
            </form>

            <form method="post" action="{{ route('two-factor.disable') }}" class="space-y-6">
                @csrf
                @method('delete')

                <div>
                    <x-input-label for="disable_two_factor_current_password" :value="__('Current Password')" />
                    <x-text-input id="disable_two_factor_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                    <x-input-error :messages="$errors->disableTwoFactor->get('current_password')" class="mt-2" />
                </div>

                <x-danger-button>{{ __('Disable Two-Factor Authentication') }}</x-danger-button>
            </form>
        </div>
    @endif

    @if (in_array(session('status'), ['two-factor-enabled', 'two-factor-confirmed', 'two-factor-disabled', 'two-factor-recovery-codes-generated'], true))
        <p
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 2500)"
            class="mt-4 text-sm text-gray-600"
        >{{ __('Saved.') }}</p>
    @endif
</section>
