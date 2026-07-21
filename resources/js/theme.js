const STORAGE_KEY = 'meditrack-theme';

export function getStoredTheme() {
    return localStorage.getItem(STORAGE_KEY);
}

export function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function resolveTheme(stored = getStoredTheme()) {
    if (stored === 'dark' || stored === 'light') {
        return stored;
    }

    return getSystemTheme();
}

export function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.dataset.theme = theme;
}

export function setTheme(theme) {
    localStorage.setItem(STORAGE_KEY, theme);
    applyTheme(theme);
}

export function toggleTheme() {
    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
    setTheme(next);

    return next;
}

export function initTheme() {
    applyTheme(resolveTheme());

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        if (! getStoredTheme()) {
            applyTheme(event.matches ? 'dark' : 'light');
        }
    });
}

initTheme();

window.MediTrackTheme = {
    getStoredTheme,
    resolveTheme,
    applyTheme,
    setTheme,
    toggleTheme,
};
