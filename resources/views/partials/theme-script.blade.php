<script>
    (function () {
        var stored = localStorage.getItem('theme');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (stored ? stored === 'dark' : prefersDark) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
