<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

/**
 * Handles all user settings including notifications and enabling, confirming, 
 * and disabling two-factor authentication for the authenticated user. Uses 
 * Fortify's built-in action classes to perform operations so logic stays 
 * consistent with Fortify's pipeline.
 */
class UserSettingsController extends Controller
{
    /**
     * Show the user settings page. Displays notification settings and 
     * the current 2FA status.
     */
    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user()->fresh();

        $hasReviewPermission = $user->organizations->contains(function ($org) use ($user) {
            return $user->hasPermissionIn($org, 'review_applications');
        });

        return view('auth.settings', compact('user', 'hasReviewPermission'));
    }

    /**
     * Enable two-factor authentication for the authenticated user. Generates
     * the secret and recovery codes but does not mark 2FA as confirmed until
     * the user verifies with their first code.
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $enable($user);

        return redirect()
            ->route('settings.show')
            ->with('success', 'Two-factor setup started. Please scan the QR code and enter the code below to finish enabling two-factor authentication.');
    }

    /**
     * Confirm two-factor authentication by verifying the first TOTP code from
     * the authenticator app. Until this step is completed 2FA is not enforced
     * on login.
     */
    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user()->fresh();

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            $confirm($user, str_replace(' ', '', $request->input('code')));
        } catch (\Throwable $e) {
            return redirect()
                ->route('settings.show')
                ->with('error', 'The code you entered is invalid. Please try again.');
        }

        return redirect()
            ->route('settings.show')
            ->with('success', 'Two-factor authentication has been confirmed and is now active.');
    }

    /**
     * Regenerate the recovery codes for the authenticated user. The previous
     * codes are invalidated immediately.
     */
    public function regenerateCodes(Request $request, GenerateNewRecoveryCodes $generate): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $generate($user);

        return redirect()
            ->route('settings.show')
            ->with('success', 'Recovery codes have been regenerated. Save them somewhere safe - the previous codes are no longer valid.');
    }
    
    /**
     * Cancels an unconfirmed 2FA setup by wiping the secret and recovery codes
     * without requiring the user to re-enter their password.
     */
    public function cancelTwoFactor(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->two_factor_confirmed_at) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
            ])->save();
        }

        return back()->with('success', 'Two-factor authentication setup has been canceled.');
    }

    /**
     * Disable two-factor authentication for the authenticated user. Clears
     * the secret and all recovery codes.
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $disable($user);

        return redirect()
            ->route('settings.show')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Toggle the notification settings for the authenticated user.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'interview_email_notifications' => ['required', 'boolean'],
            'application_email_notifications' => ['required', 'boolean'],
        ]);

        $user->update([
            'interview_email_notifications' => $validated['interview_email_notifications'],
            'application_email_notifications' => $validated['application_email_notifications'],
        ]);

        return redirect()
            ->route('settings.show')
            ->with('success', 'Notification settings updated.');
    }
}