<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Settings</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div>
        <h1>Two-Factor Authentication</h1>

        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        @if (! $user->hasEnabledTwoFactorAuthentication())
            {{-- 2FA is not enabled --}}
            <p>
                Two-factor authentication is not enabled. When enabled, you will
                be prompted for a secure, random token during authentication. You
                may retrieve this token from your authenticator application.
            </p>

            <form method="POST" action="{{ route('two-factor.store') }}">
                @csrf

                <div>
                    <label for="password">Confirm your password to enable 2FA</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit">Enable Two-Factor Authentication</button>
            </form>

        @elseif (! $user->two_factor_confirmed_at)
            {{-- 2FA is enabled but not yet confirmed --}}
            <p>
                Scan the QR code below with your authenticator app, then enter
                the generated code to confirm and activate two-factor
                authentication.
            </p>

            <div>
                {!! $user->twoFactorQrCodeSvg() !!}
            </div>

            <p>
                If you cannot scan the QR code, enter this setup key manually
                in your authenticator app:
            </p>
            <code>{{ decrypt($user->two_factor_secret) }}</code>

            <form method="POST" action="{{ route('two-factor.confirm') }}">
                @csrf

                <div>
                    <label for="code">Authenticator Code</label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        inputmode="numeric"
                        required
                        autofocus
                        autocomplete="one-time-code"
                    >
                </div>

                <button type="submit">Confirm</button>
            </form>

        @else
            {{-- 2FA is fully enabled and confirmed --}}
            <p>
                Two-factor authentication is active on your account.
            </p>

            <h2>Recovery Codes</h2>
            <p>
                Store these recovery codes in a secure location. They can be used
                to recover access to your account if your authenticator device is
                lost. Each code can only be used once.
            </p>

            <ul>
                @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                    <li><code>{{ $code }}</code></li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route('two-factor.regenerate') }}">
                @csrf
                @method('PUT')

                <div>
                    <label for="regen_password">Confirm your password to regenerate codes</label>
                    <input
                        id="regen_password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit">Regenerate Recovery Codes</button>
            </form>

            <h2>Disable Two-Factor Authentication</h2>

            <form method="POST" action="{{ route('two-factor.destroy') }}">
                @csrf
                @method('DELETE')

                <div>
                    <label for="disable_password">Confirm your password to disable 2FA</label>
                    <input
                        id="disable_password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit">Disable Two-Factor Authentication</button>
            </form>
        @endif

        <a href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>
</body>
</html>