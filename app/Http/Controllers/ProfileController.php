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
use Illuminate\Support\Facades\Storage;
use App\Models\User;
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

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], ['avatar' => 'immagine di profilo']);

        $user = $request->user();
        $vecchio = $user->avatar_path;
        $user->avatar_path = $request->file('avatar')->store('avatars', 'local');
        $user->save();

        if ($vecchio) {
            Storage::disk('local')->delete($vecchio);
        }

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('local')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'avatar-removed');
    }

    public function showAvatar(User $user)
    {
        abort_unless($user->avatar_path && Storage::disk('local')->exists($user->avatar_path), 404);

        return response()->file(Storage::disk('local')->path($user->avatar_path), [
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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
