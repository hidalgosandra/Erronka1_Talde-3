(() => {
    const systemTheme = window.matchMedia?.('(prefers-color-scheme: dark)');
    let selectedTheme = null;
    const readPreference = () => {
        try {
            const saved = localStorage.getItem('eskolak-theme');
            return saved === 'dark' || saved === 'light' ? saved : null;
        } catch {
            return selectedTheme;
        }
    };
    selectedTheme = readPreference();

    const options = () => [...(document.querySelector('[data-theme-toggle]')?.querySelectorAll('[data-set-theme]') ?? [])];
    const applyTheme = () => {
        const theme = selectedTheme ?? (systemTheme?.matches ? 'dark' : 'light');
        document.documentElement.dataset.theme = theme;
        document.querySelector('[data-theme-color]')?.setAttribute('content', theme === 'dark' ? '#000000' : '#f7f8f2');
        options().forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.setTheme === theme)));
    };
    const initializeControls = () => {
        applyTheme();
        options().forEach((button) => button.addEventListener('click', () => {
            selectedTheme = button.dataset.setTheme === 'dark' ? 'dark' : 'light';
            applyTheme();
            try {
                localStorage.setItem('eskolak-theme', selectedTheme);
            } catch { /* Keep the choice in memory when storage is unavailable. */ }
        }));
    };

    // Apply before painting, then synchronize controls as soon as the DOM exists.
    applyTheme();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeControls, { once: true });
    } else {
        initializeControls();
    }
    window.addEventListener('pageshow', () => {
        selectedTheme = readPreference();
        applyTheme();
    });
    window.addEventListener('storage', (event) => {
        if (event.key === 'eskolak-theme' || event.key === null) {
            selectedTheme = readPreference();
            applyTheme();
        }
    });
    systemTheme?.addEventListener('change', applyTheme);
})();
