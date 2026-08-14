<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\TwoFactorAuthenticator;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, TwoFactorAuthenticator $authenticator): View
    {
        $user = $request->user();
        $secret = $user->two_factor_secret
            ? $authenticator->decryptSecret((string) $user->two_factor_secret)
            : null;

        $recoveryCodes = $user->two_factor_recovery_codes
            ? $authenticator->decryptRecoveryCodes((string) $user->two_factor_recovery_codes)
            : [];

        $provisioningUri = $secret
            ? $authenticator->provisioningUri((string) config('app.name', 'Laravel'), (string) $user->email, $secret)
            : null;

        $twoFactorQrSvg = null;

        if ($provisioningUri) {
            try {
                $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());
                $twoFactorQrSvg = (new Writer($renderer))->writeString($provisioningUri);
            } catch (Throwable) {
                $twoFactorQrSvg = null;
            }
        }

        return view('profile.edit', [
            'user' => $user,
            'twoFactorEnabled' => $user->hasTwoFactorEnabled(),
            'twoFactorPendingConfirmation' => ! $user->hasTwoFactorEnabled() && ! is_null($secret),
            'twoFactorSecret' => $secret,
            'twoFactorProvisioningUri' => $provisioningUri,
            'twoFactorQrSvg' => $twoFactorQrSvg,
            'twoFactorRecoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
