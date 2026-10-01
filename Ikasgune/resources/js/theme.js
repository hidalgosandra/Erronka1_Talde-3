(() => {
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
    const themeLocked = document.documentElement.dataset.themeLocked === 'true';

    const options = () => [...(document.querySelector('[data-theme-toggle]')?.querySelectorAll('[data-set-theme]') ?? [])];
    const applyTheme = () => {
        const theme = themeLocked ? 'light' : (selectedTheme ?? 'light');
        document.documentElement.dataset.theme = theme;
        document.querySelector('[data-theme-color]')?.setAttribute('content', theme === 'dark' ? '#000000' : '#f7f8f2');
        options().forEach((button) => {
            const isSelected = button.dataset.setTheme === theme;
            button.setAttribute('aria-pressed', String(isSelected));
            button.classList.toggle('is-selected', isSelected);
            button.disabled = themeLocked;
            button.setAttribute('aria-disabled', String(themeLocked));
        });
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

    const selectTheme = (target) => {
        if (themeLocked) return;
        const option = target?.closest?.('[data-set-theme]') ?? (target?.dataset?.setTheme ? target : null);
        const theme = option?.dataset?.setTheme;
        if (theme !== 'dark' && theme !== 'light') return;

        selectedTheme = theme;
        applyTheme();
        try {
            localStorage.setItem('eskolak-theme', selectedTheme);
        } catch { /* Keep the choice in memory when storage is unavailable. */ }
    };

    document.addEventListener('click', (event) => selectTheme(event.target));

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
})();
