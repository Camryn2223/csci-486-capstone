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
 * Handles enabling, confirming, and disabling two-factor authentication for
 * the authenticated user. Uses Fortify's built-in action classes to perform
 * each operation so that the logic stays consistent with Fortify's pipeline.
 *
 * The flow is:
 *   1. User visits settings and clicks "Enable 2FA" -> store() enables it and
 *      shows the QR code.
 *   2. User scans the QR code with their authenticator app and submits the
 *      first code -> confirm() marks 2FA as confirmed.
 *   3. User can view recovery codes at any time from settings.
 *   4. User can regenerate recovery codes -> regenerateCodes().
 *   5. User can disable 2FA -> destroy().
 */
class TwoFactorController extends Controller
{
    /**
     * Show the two-factor authentication settings page. Displays the current
     * 2FA status, QR code if enabled but not yet confirmed, and recovery codes
     * if fully confirmed.
     */
    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user()->fresh();

        $hasReviewPermission = $user->organizations->contains(function ($org) use ($user) {
            return $user->hasPermissionIn($org, 'review_applications');
        });

        return view('auth.two-factor', compact('user', 'hasReviewPermission'));
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
            ->route('two-factor.show')
            ->with('success', 'Two-factor authentication has been enabled. Scan the QR code with your authenticator app then enter a code to confirm.');
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
                ->route('two-factor.show')
                ->with('error', 'The code you entered is invalid. Please try again.');
        }

        return redirect()
            ->route('two-factor.show')
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
            ->route('two-factor.show')
            ->with('success', 'Recovery codes have been regenerated. Save them somewhere safe - the previous codes are no longer valid.');
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
            ->route('two-factor.show')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Toggle the interview email notifications setting for the authenticated user.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'interview_email_notifications' => ['required', 'boolean'],
        ]);

        $user->update([
            'interview_email_notifications' => $validated['interview_email_notifications'],
        ]);

        return redirect()
            ->route('two-factor.show')
            ->with('success', 'Notification settings updated.');
    }
}