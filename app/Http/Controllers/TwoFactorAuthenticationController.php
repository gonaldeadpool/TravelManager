<?php

namespace App\Http\Controllers;

use App\Support\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthenticationController extends Controller
{
    public function enable(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $request->validateWithBag('enableTwoFactor', [
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $secret = $authenticator->generateSecret();
        $recoveryCodes = $authenticator->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $authenticator->encryptSecret($secret),
            'two_factor_recovery_codes' => $authenticator->encryptRecoveryCodes($recoveryCodes),
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-enabled');
    }

    /**
     * @throws ValidationException
     */
    public function confirm(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $request->validateWithBag('confirmTwoFactor', [
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            throw ValidationException::withMessages([
                'code' => __('Two-factor authentication is not enabled for this account.'),
            ])->errorBag('confirmTwoFactor');
        }

        $secret = $authenticator->decryptSecret((string) $user->two_factor_secret);

        if ($secret === null || ! $authenticator->verify($secret, (string) $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => __('The provided authentication code is invalid.'),
            ])->errorBag('confirmTwoFactor');
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('status', 'two-factor-confirmed');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validateWithBag('disableTwoFactor', [
            'current_password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-disabled');
    }

    public function regenerateRecoveryCodes(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $request->validateWithBag('recoveryCodes', [
            'current_password' => ['required', 'current_password'],
        ]);

        if (! $request->user()->hasTwoFactorEnabled()) {
            return back();
        }

        $request->user()->forceFill([
            'two_factor_recovery_codes' => $authenticator->encryptRecoveryCodes($authenticator->generateRecoveryCodes()),
        ])->save();

        return back()->with('status', 'two-factor-recovery-codes-generated');
    }
}
