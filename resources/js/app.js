import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('searchSuggestions', (endpoint, initial = '') => ({
        query: initial,
        suggestions: [],
        open: false,
        timer: null,

        search() {
            clearTimeout(this.timer);
            const q = this.query.trim();

            if (q.length < 1) {
                this.suggestions = [];
                this.open = false;
                return;
            }

            this.timer = setTimeout(() => {
                fetch(`${endpoint}?q=${encodeURIComponent(q)}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                })
                    .then((response) => (response.ok ? response.json() : []))
                    .then((data) => {
                        this.suggestions = data;
                        this.open = data.length > 0;
                    })
                    .catch(() => {
                        this.suggestions = [];
                        this.open = false;
                    });
            }, 250);
        },

        select(value) {
            this.query = value;
            this.open = false;
            this.$refs.form.submit();
        },
    }));
});

Alpine.start();
