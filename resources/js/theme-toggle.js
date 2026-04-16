document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('theme-toggle');
    const root = document.documentElement;
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    // Listen for OS/Browser theme changes and update automatically 
    // IF the user hasn't manually overridden it using the toggle.
    mediaQuery.addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            root.classList.toggle('dark', e.matches);
        }
    });
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            root.classList.toggle('dark');
            const isDark = root.classList.contains('dark');
            
            // Setting this disables automatic OS syncing going forward 
            // since they explicitly chose a preference
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    }
});