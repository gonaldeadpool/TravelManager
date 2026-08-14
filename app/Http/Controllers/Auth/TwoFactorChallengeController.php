<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TwoFactorAuthenticator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor.login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        if (! $request->session()->has('two_factor.login.id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = User::query()->find($request->session()->get('two_factor.login.id'));

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['two_factor.login.id', 'two_factor.remember']);

            return redirect()->route('login');
        }

        $code = (string) $request->input('code', '');
        $recoveryCode = trim((string) $request->input('recovery_code', ''));

        $validCode = false;

        if ($code !== '') {
            $secret = $authenticator->decryptSecret((string) $user->two_factor_secret);
            $validCode = $secret !== null && $authenticator->verify($secret, $code);
        }

        if (! $validCode && $recoveryCode !== '') {
            $recoveryCodes = $authenticator->decryptRecoveryCodes((string) $user->two_factor_recovery_codes);
            $matched = array_search($recoveryCode, $recoveryCodes, true);

            if ($matched !== false) {
                unset($recoveryCodes[$matched]);

                $user->forceFill([
                    'two_factor_recovery_codes' => $authenticator->encryptRecoveryCodes(array_values($recoveryCodes)),
                ])->save();

                $validCode = true;
            }
        }

        if (! $validCode) {
            throw ValidationException::withMessages([
                'code' => __('The provided authentication code is invalid.'),
            ]);
        }

        $remember = (bool) $request->session()->pull('two_factor.remember', false);

        $request->session()->forget('two_factor.login.id');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
