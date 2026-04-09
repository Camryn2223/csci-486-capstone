document.addEventListener('DOMContentLoaded', function() {
    const appForm = document.getElementById('application-form');
    if (!appForm) return;

    appForm.addEventListener('submit', function(e) {
        const fileInputs = document.querySelectorAll('.tracked-upload');
        let tooLarge = false;
        
        const SAFE_MB_LIMIT = 7.5; 

        fileInputs.forEach(input => {
            if (input.files && input.files.length > 0) {
                const fileSizeMB = input.files[0].size / 1024 / 1024;
                if (fileSizeMB > SAFE_MB_LIMIT) {
                    tooLarge = true;
                }
            }
        });

        if (tooLarge) {
            e.preventDefault();
            alert('One or more selected files are too large! Please ensure each file is strictly under ' + SAFE_MB_LIMIT + 'MB to prevent the server from rejecting your application.');
        }
    });
});