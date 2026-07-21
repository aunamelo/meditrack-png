<script>
    (function () {
        const key = 'meditrack-theme';
        const stored = localStorage.getItem(key);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = stored === 'dark' || stored === 'light'
            ? stored
            : (prefersDark ? 'dark' : 'light');

        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        }

        document.documentElement.dataset.theme = theme;
    })();
</script>
