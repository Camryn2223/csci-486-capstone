@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Two-Factor Authentication</h1>
        <a href="{{ route('dashboard') }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Dashboard</a>
    </div>

    <div class="card">
        @if (! $user->two_factor_secret)
            {{-- 2FA not enabled --}}
            <p style="color: #bdbdbd; font-size: 16px;">Two-factor authentication is not enabled on your account.</p>

            <form method="POST" action="{{ route('two-factor.store') }}">
                @csrf
                <label>Confirm your password to enable 2FA</label>
                <input type="password" name="password" required autocomplete="current-password">
                <button type="submit" class="btn">Enable Two-Factor Authentication</button>
            </form>

        @elseif (! $user->two_factor_confirmed_at)
            {{-- 2FA enabled, QR code not yet confirmed --}}

            <p style="color: #e6e6e6; font-size: 16px;">Scan the QR code below with your authenticator app, then enter the generated code to confirm.</p>

            <div style="background: white; padding: 15px; display: inline-block; border-radius: 6px; margin: 15px 0;">
                {!! $user->twoFactorQrCodeSvg() !!}
            </div>

            <p style="color: #bdbdbd;">Or enter this setup key manually: <code style="color: #a97dff; font-size: 16px;">{{ decrypt($user->two_factor_secret) }}</code></p>

            <form method="POST" action="{{ route('two-factor.confirm') }}" style="margin-top: 25px;">
                @csrf
                <label>Authenticator Code</label>
                <input type="text" name="code" inputmode="numeric" required autofocus autocomplete="one-time-code" style="max-width: 300px;">
                @error('code')
                    <span style="color: #ff9d9d; display: block; margin-top: -10px; margin-bottom: 15px;">{{ $message }}</span>
                @enderror
                <button type="submit" class="btn">Confirm Setup</button>
            </form>

        @else
            {{-- Active --}}
            <p style="font-size: 16px;"><span class="status status-complete">Active</span> Two-factor authentication is active on your account.</p>

            <hr style="border-color: #3a3f45; margin: 25px 0;">

            <h2 style="margin-top: 0;">Recovery Codes</h2>
            <p style="color: #bdbdbd;">Store these in a safe place. Each can only be used once.</p>
            <div style="background: #1f2327; padding: 20px; border-radius: 6px; border: 1px solid #3a3f45; font-family: monospace; font-size: 16px; margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                    <div>{{ $code }}</div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('two-factor.regenerate') }}">
                @csrf
                @method('PUT')
                <label>Confirm password to regenerate codes</label>
                <div style="display: flex; gap: 10px; max-width: 400px; align-items: flex-start;">
                    <input type="password" name="password" required autocomplete="current-password" style="margin-bottom: 0;">
                    <button type="submit" class="btn" style="background: #2f3a4a; white-space: nowrap;">Regenerate Codes</button>
                </div>
            </form>

            <hr style="border-color: #3a3f45; margin: 25px 0;">

            <h2 style="margin-top: 0; color: #ff9d9d;">Disable 2FA</h2>
            <form method="POST" action="{{ route('two-factor.destroy') }}">
                @csrf
                @method('DELETE')
                <label>Confirm password to disable</label>
                <div style="display: flex; gap: 10px; max-width: 400px; align-items: flex-start;">
                    <input type="password" name="password" required autocomplete="current-password" style="margin-bottom: 0;">
                    <button type="submit" class="btn btn-danger" style="white-space: nowrap;">Disable 2FA</button>
                </div>
            </form>

        @endif
    </div>
</div>
@endsection