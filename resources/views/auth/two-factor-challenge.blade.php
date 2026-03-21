@extends('layouts.app')

@section('content')
    <h1>Two-Factor Authentication</h1>

    <p>Open your authenticator app and enter the code, or enter one of your recovery codes.</p>

    <form method="POST" action="{{ route('two-factor.login') }}" id="totp-form">
        @csrf

        <div id="totp-section">
            <label>Authenticator Code<br>
                <input type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code">
            </label>
        </div>

        <div id="recovery-section" style="display:none">
            <label>Recovery Code<br>
                <input type="text" name="recovery_code" autocomplete="one-time-code">
            </label>
        </div>

        <br>
        <button type="button" id="toggle-recovery">Use a recovery code instead</button>
        <button type="submit">Verify</button>
    </form>

    <script>
        const totpSection = document.getElementById('totp-section');
        const recoverySection = document.getElementById('recovery-section');
        const toggleBtn = document.getElementById('toggle-recovery');
        const codeInput = document.querySelector('[name="code"]');
        const recoveryInput = document.querySelector('[name="recovery_code"]');
        let usingRecovery = false;

        recoveryInput.disabled = true;

        toggleBtn.addEventListener('click', () => {
            usingRecovery = !usingRecovery;
            totpSection.style.display = usingRecovery ? 'none' : 'block';
            recoverySection.style.display = usingRecovery ? 'block' : 'none';
            toggleBtn.textContent = usingRecovery ? 'Use an authenticator code instead' : 'Use a recovery code instead';
            codeInput.disabled = usingRecovery;
            recoveryInput.disabled = !usingRecovery;
            (usingRecovery ? recoveryInput : codeInput).focus();
        });
    </script>
@endsection