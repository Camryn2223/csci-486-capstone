<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div>
        <h1>Two-Factor Authentication</h1>

        <p>
            Open your authenticator app and enter the code for this account, or
            enter one of your recovery codes.
        </p>

        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('two-factor.login') }}" id="totp-form">
            @csrf

            <div id="totp-section">
                <label for="code">Authenticator Code</label>
                <input
                    id="code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    autofocus
                    autocomplete="one-time-code"
                >
            </div>

            <div id="recovery-section" style="display: none;">
                <label for="recovery_code">Recovery Code</label>
                <input
                    id="recovery_code"
                    type="text"
                    name="recovery_code"
                    autocomplete="one-time-code"
                >
            </div>

            <div>
                <button type="button" id="toggle-recovery">
                    Use a recovery code instead
                </button>

                <button type="submit">Verify</button>
            </div>
        </form>
    </div>

    <script>
        const totpSection = document.getElementById('totp-section');
        const recoverySection = document.getElementById('recovery-section');
        const toggleBtn = document.getElementById('toggle-recovery');
        const codeInput = document.getElementById('code');
        const recoveryInput = document.getElementById('recovery_code');

        let usingRecovery = false;

        toggleBtn.addEventListener('click', () => {
            usingRecovery = !usingRecovery;

            totpSection.style.display = usingRecovery ? 'none' : 'block';
            recoverySection.style.display = usingRecovery ? 'block' : 'none';
            toggleBtn.textContent = usingRecovery
                ? 'Use an authenticator code instead'
                : 'Use a recovery code instead';

            codeInput.disabled = usingRecovery;
            recoveryInput.disabled = !usingRecovery;

            if (usingRecovery) {
                recoveryInput.focus();
            } else {
                codeInput.focus();
            }
        });

        recoveryInput.disabled = true;
    </script>
</body>
</html>