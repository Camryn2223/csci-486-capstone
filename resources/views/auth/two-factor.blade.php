@extends('layouts.app')

@section('content')
    <h1>Two-Factor Authentication</h1>

    @if (! $user->two_factor_secret)
        {{-- 2FA not enabled --}}
        <p>Two-factor authentication is not enabled on your account.</p>

        <form method="POST" action="{{ route('two-factor.store') }}">
            @csrf
            <label>Confirm your password to enable 2FA<br>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <br><br>
            <button type="submit">Enable Two-Factor Authentication</button>
        </form>

    @elseif (! $user->two_factor_confirmed_at)
        {{-- 2FA enabled, QR code not yet confirmed --}}

        <p>Scan the QR code below with your authenticator app, then enter the generated code to confirm.</p>

        {!! $user->twoFactorQrCodeSvg() !!}

        <p>Or enter this setup key manually: <code>{{ decrypt($user->two_factor_secret) }}</code></p>

        <form method="POST" action="{{ route('two-factor.confirm') }}">
            @csrf
            <label>Authenticator Code<br>
                <input type="text" name="code" inputmode="numeric" required autofocus autocomplete="one-time-code">
            </label>
            @error('code')
                <br><span style="color:red">{{ $message }}</span>
            @enderror
            <br><br>
            <button type="submit">Confirm</button>
        </form>

    @else

        <p>Two-factor authentication is <strong>active</strong> on your account.</p>

        <h2>Recovery Codes</h2>
        <p>Store these in a safe place. Each can only be used once.</p>
        <ul>
            @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                <li><code>{{ $code }}</code></li>
            @endforeach
        </ul>

        <form method="POST" action="{{ route('two-factor.regenerate') }}">
            @csrf
            @method('PUT')
            <label>Confirm password to regenerate codes<br>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <br><br>
            <button type="submit">Regenerate Recovery Codes</button>
        </form>

        <h2>Disable 2FA</h2>
        <form method="POST" action="{{ route('two-factor.destroy') }}">
            @csrf
            @method('DELETE')
            <label>Confirm password to disable<br>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <br><br>
            <button type="submit">Disable Two-Factor Authentication</button>
        </form>

    @endif

    <br><a href="{{ route('dashboard') }}">Back to Dashboard</a>
@endsection