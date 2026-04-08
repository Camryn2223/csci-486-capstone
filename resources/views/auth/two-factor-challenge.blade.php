@extends('layouts.app')

@section('content')
    <div class="centered-content">
        <div class="form-box" style="width: 450px;">
            <h2>Two-Factor Authentication</h2>

            <p style="color: #bdbdbd; text-align: center; font-size: 14px; margin-bottom: 20px;">Open your authenticator app and enter the code, or enter one of your recovery codes.</p>

            <form method="POST" action="{{ route('two-factor.login') }}" id="totp-form">
                @csrf

                <div id="totp-section">
                    <label>Authenticator Code</label>
                    <input type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code">
                </div>

                <div id="recovery-section" style="display:none">
                    <label>Recovery Code</label>
                    <input type="text" name="recovery_code" autocomplete="one-time-code">
                </div>

                <button type="submit" class="btn" style="margin-bottom: 10px;">Verify</button>
                <button type="button" class="btn" id="toggle-recovery" style="background: #24282d; border: 1px solid #3a3f45;">Use a recovery code instead</button>
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
        </div>
    </div>
@endsection