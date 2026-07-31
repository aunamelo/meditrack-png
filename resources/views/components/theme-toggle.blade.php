@props([
    'compact' => false,
])

<button
    type="button"
    {{ $attributes->merge([
        'class' => $compact
            ? 'inline-flex items-center justify-center rounded-lg border border-line p-2 text-ink-muted transition hover:bg-surface-muted hover:text-brand-600 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-brand-400'
            : 'inline-flex items-center justify-center rounded-full bg-brand-600 p-3.5 text-white shadow-soft transition hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-400',
    ]) }}
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            if (window.MediTrackTheme?.toggleTheme) {
                this.dark = window.MediTrackTheme.toggleTheme() === 'dark';
                return;
            }

            const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            document.documentElement.classList.toggle('dark', next === 'dark');
            document.documentElement.dataset.theme = next;
            localStorage.setItem('meditrack-theme', next);
            this.dark = next === 'dark';
        },
    }"
    @click="toggle()"
    aria-label="Toggle dark mode"
>
    <svg x-show="dark" x-cloak class="{{ $compact ? 'h-5 w-5' : 'h-5 w-5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <svg x-show="!dark" x-cloak class="{{ $compact ? 'h-5 w-5' : 'h-5 w-5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
    </svg>
</button>
