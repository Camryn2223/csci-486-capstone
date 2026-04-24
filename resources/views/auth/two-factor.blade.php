@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">Two-Factor Authentication</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline">Back to Organization</a>
    </div>

    <div class="card">
        @if (! $user->two_factor_secret)
            <p class="text-muted fs-16">Two-factor authentication is not enabled on your account.</p>

            <form method="POST" action="{{ route('two-factor.store') }}">
                @csrf
                <label>Confirm your password to enable 2FA</label>
                <input type="password" name="password" required autocomplete="current-password">
                <button type="submit" class="btn">Enable Two-Factor Authentication</button>
            </form>

        @elseif (! $user->two_factor_confirmed_at)
            <p class="text-light fs-16">Scan the QR code below with your authenticator app, then enter the generated code to confirm.</p>

            <div class="qr-container">
                {!! $user->twoFactorQrCodeSvg() !!}
            </div>

            <p class="text-muted">Or enter this setup key manually: <code class="text-primary fs-16">{{ decrypt($user->two_factor_secret) }}</code></p>

            <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-25">
                @csrf
                <label>Authenticator Code</label>
                <input type="text" name="code" inputmode="numeric" required autofocus autocomplete="one-time-code" class="max-w-300">
                @error('code')
                    <span class="text-danger d-block mb-15" style="margin-top: -10px;">{{ $message }}</span>
                @enderror
                <button type="submit" class="btn">Confirm Setup</button>
            </form>

        @else
            <p class="fs-16"><span class="status status-complete">Active</span> Two-factor authentication is active on your account.</p>

            <hr class="divider">

            <h2 class="mt-0">Recovery Codes</h2>
            <p class="text-muted">Store these in a safe place. Each can only be used once.</p>
            <div class="recovery-codes-box">
                @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                    <div>{{ $code }}</div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('two-factor.regenerate') }}">
                @csrf
                @method('PUT')
                <label>Confirm password to regenerate codes</label>
                <div class="form-inline-start max-w-400">
                    <input type="password" name="password" required autocomplete="current-password" class="mb-0">
                    <button type="submit" class="btn btn-slate white-space-nowrap">Regenerate Codes</button>
                </div>
            </form>

            <hr class="divider">

            <h2 class="mt-0 text-danger">Disable 2FA</h2>
            <form method="POST" action="{{ route('two-factor.destroy') }}">
                @csrf
                @method('DELETE')
                <label>Confirm password to disable</label>
                <div class="form-inline-start max-w-400">
                    <input type="password" name="password" required autocomplete="current-password" class="mb-0">
                    <button type="submit" class="btn btn-danger white-space-nowrap">Disable 2FA</button>
                </div>
            </form>

        @endif
    </div>
</div>
@endsection