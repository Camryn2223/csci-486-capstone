<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles user registration. Login, logout, email verification, password
 * reset, and the two-factor authentication challenge are all handled by
 * Laravel Fortify — this controller only covers the registration flow since
 * Fortify's built-in registration does not assign a role.
 */
class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request. Creates the user with the default
     * interviewer role and logs them in immediately. The chairman role can
     * only be assigned by an existing chairman via the user management
     * interface — it cannot be self-selected at registration.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'role'     => 'interviewer',
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}