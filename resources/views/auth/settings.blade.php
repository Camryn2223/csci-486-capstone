@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">User Settings</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline">Back to Dashboard</a>
    </div>

    {{-- Notifications Section --}}
    @if ($hasReviewPermission)
        <div class="card">
            <h2 class="mt-0">Notifications</h2>

            <form method="POST" action="{{ route('settings.notifications') }}" class="m-0 flex-col-15">
                @csrf
                @method('PATCH')
                
                <div class="d-flex items-center flex-gap-10">
                    <input type="hidden" name="interview_email_notifications" value="0">
                    <input type="checkbox" name="interview_email_notifications" value="1" id="interview-notif" {{ $user->interview_email_notifications ? 'checked' : '' }}>
                    <label for="interview-notif" class="m-0" style="cursor: pointer;">Email me when I am scheduled as an interviewer</label>
                </div>

                <div class="d-flex items-center flex-gap-10">
                    <input type="hidden" name="application_email_notifications" value="0">
                    <input type="checkbox" name="application_email_notifications" value="1" id="application-notif" {{ $user->application_email_notifications ? 'checked' : '' }}>
                    <label for="application-notif" class="m-0" style="cursor: pointer;">Email me when an application is submitted that I can review</label>
                </div>

                <div>
                    <button type="submit" class="btn btn-sm">Save Notification Settings</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Two-Factor Authentication Section --}}
    <div class="card">
        <h2 class="mt-0">Two-Factor Authentication</h2>

        @if (! $user->two_factor_secret)
            <p class="text-muted fs-16">Two-factor authentication is not enabled on your account.</p>

            <form method="POST" action="{{ route('settings.store') }}">
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

            <form method="POST" action="{{ route('settings.confirm') }}" class="mt-25">
                @csrf
                <label>Authenticator Code</label>
                <input type="text" name="code" inputmode="numeric" required autofocus autocomplete="one-time-code" class="max-w-300">
                @error('code')
                    <span class="text-danger d-block mb-15" style="margin-top: -10px;">{{ $message }}</span>
                @enderror
                
                <div class="d-flex flex-gap-10 mt-10">
                    <button type="submit" class="btn m-0">Confirm Setup</button>
                </div>
            </form>
            
            <form method="POST" action="{{ route('settings.cancel-setup') }}" class="m-0 mt-15">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline text-danger m-0" style="border-color: var(--danger-border);">Cancel Setup</button>
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

            <form method="POST" action="{{ route('settings.regenerate') }}">
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
            <form method="POST" action="{{ route('settings.destroy') }}">
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