<?php

namespace App\Http\Controllers;

use App\Models\OrganizationInvite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles user registration. Login, logout, email verification, password
 * reset, and the two-factor authentication challenge are all handled by
 * Laravel Fortify - this controller only covers the registration flow.
 *
 * Registration behaviour:
 *   - If no users exist yet, the first registrant becomes a chairman with no
 *     invite code required.
 *   - All subsequent registrants must supply a valid unused invite code.
 *     They are added to the invite's organization as an interviewer on signup
 *     and the invite is marked as used.
 *   - Invite links from emails pre-fill the code via a query parameter so
 *     the recipient does not have to type it manually.
 */
class AuthController extends Controller
{
    /**
     * Show the registration form. If an invite code is present in the query
     * string (from an emailed invite link), pre-fill and lock it in the form.
     */
    public function create(Request $request): View
    {
        $isFirstUser  = User::count() === 0;
        $inviteCode   = $request->query('invite');
        $inviteValid  = $inviteCode ? (bool) OrganizationInvite::findUnused($inviteCode) : false;

        return view('auth.register', compact('isFirstUser', 'inviteCode', 'inviteValid'));
    }

    /**
     * Handle a registration request. Marks the invite as used after the user
     * is successfully created.
     */
    public function store(Request $request): RedirectResponse
    {
        $isFirstUser = User::count() === 0;

        $rules = [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ];

        if (! $isFirstUser) {
            $rules['invite_code'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        if (! $isFirstUser) {
            $invite = OrganizationInvite::findUnused($validated['invite_code']);

            if (! $invite) {
                return back()->withErrors([
                    'invite_code' => 'This invite code is invalid or has already been used.',
                ])->withInput();
            }
        }

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'role'     => $isFirstUser ? 'chairman' : 'interviewer',
        ]);

        if (! $isFirstUser) {
            $invite->organization->members()->attach($user->id);
            $invite->markUsed();
        }

        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}