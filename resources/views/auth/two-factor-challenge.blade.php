@extends('layouts.app')

@section('content')
    <div class="centered-content">
        <div class="form-box form-box-2fa">
            <h2>Two-Factor Authentication</h2>

            <p class="text-muted text-center fs-14 mb-20">Open your authenticator app and enter the code, or enter one of your recovery codes.</p>

            <form method="POST" action="{{ route('two-factor.login') }}" id="totp-form">
                @csrf

                <div id="totp-section">
                    <label>Authenticator Code</label>
                    <input type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code">
                </div>

                <div id="recovery-section" class="d-none">
                    <label>Recovery Code</label>
                    <input type="text" name="recovery_code" autocomplete="one-time-code">
                </div>

                <button type="submit" class="btn mb-10">Verify</button>
                <button type="button" class="btn btn-outline w-full mt-0" id="toggle-recovery">Use a recovery code instead</button>
            </form>
        </div>
    </div>
@endsection