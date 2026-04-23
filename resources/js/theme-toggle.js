document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('theme-toggle');
    const root = document.documentElement;
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    const notifyThemeChange = () => {
        document.dispatchEvent(new CustomEvent('themechange', {
            detail: {
                dark: root.classList.contains('dark')
            }
        }));
    };

    mediaQuery.addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            root.classList.toggle('dark', e.matches);
            notifyThemeChange();
        }
    });

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            root.classList.toggle('dark');
            const isDark = root.classList.contains('dark');

            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            notifyThemeChange();
        });
    }
});