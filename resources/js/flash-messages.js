document.addEventListener("DOMContentLoaded", function() {
    const flashMessages = document.querySelectorAll('.flash-success, .toast.flash-error');
    flashMessages.forEach(function(msg) {
        setTimeout(function() {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 5000);
    });
});