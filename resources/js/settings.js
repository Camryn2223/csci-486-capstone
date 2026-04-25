document.addEventListener("DOMContentLoaded", () => {
    const totpSection = document.getElementById('totp-section');
    const recoverySection = document.getElementById('recovery-section');
    const toggleBtn = document.getElementById('toggle-recovery');
    
    if (!totpSection || !recoverySection || !toggleBtn) return;
    
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
});