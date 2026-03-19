<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

/**
 * Registers Laravel Fortify's view callbacks so that each auth route renders
 * the correct Blade template. Also configures login rate limiting.
 */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));

        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));

        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));

        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));

        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        Fortify::redirects('login', '/dashboard');
        Fortify::redirects('register', '/dashboard');
        Fortify::redirects('email-verification', '/dashboard');
        Fortify::redirects('password-reset', '/dashboard');

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = strtolower($request->input(Fortify::username())) . '|' . $request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}