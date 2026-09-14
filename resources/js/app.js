import Alpine from 'alpinejs';
import coalProductForm from './coal-product-form.js';

window.Alpine = Alpine;
Alpine.data('coalProductForm', coalProductForm);

document.addEventListener('alpine:init', () => {
    Alpine.data('liveTable', (baseUrl, initialFilters = {}) => ({
        url: baseUrl,
        filters: initialFilters,
        isLoading: false,
        debounceTimer: null,

        init() {
            // Watch any filter changes and automatically debounce-fetch
            this.$watch('filters', () => {
                this.debounceFetch();
            });
        },

        debounceFetch() {
            this.isLoading = true;
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.fetchData();
            }, 300);
        },

        async fetchData(customUrl = null) {
            this.isLoading = true;
            try {
                let targetUrl = customUrl;
                if (!targetUrl) {
                    const params = new URLSearchParams();
                    for (const [key, value] of Object.entries(this.filters)) {
                        if (value !== '' && value !== null && value !== undefined) {
                            params.append(key, value);
                        }
                    }
                    const queryString = params.toString();
                    targetUrl = this.url + (queryString ? '?' + queryString : '');
                }

                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });

                if (response.ok) {
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newTable = doc.querySelector('#live-table-container');
                    const currentTable = document.querySelector('#live-table-container');
                    
                    if (newTable && currentTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    }

                    window.history.replaceState({}, '', targetUrl);
                }
            } catch (error) {
                console.error('Live search error:', error);
            } finally {
                this.isLoading = false;
            }
        },

        resetFilters() {
            for (const key of Object.keys(this.filters)) {
                this.filters[key] = '';
            }
            this.fetchData();
        },

        hasActiveFilters() {
            return Object.values(this.filters).some(val => val !== '' && val !== null && val !== undefined);
        }
    }));
});

// Intercept pagination clicks inside live table container for AJAX pagination
document.addEventListener('click', (e) => {
    const link = e.target.closest('#live-table-container a[href*="page="], #live-table-container .pagination a');
    if (link && link.href) {
        e.preventDefault();
        const liveTableEl = document.querySelector('[x-data*="liveTable"]');
        if (liveTableEl && liveTableEl._x_dataStack) {
            const tableData = liveTableEl._x_dataStack[0];
            if (tableData && typeof tableData.fetchData === 'function') {
                tableData.fetchData(link.href);
            }
        }
    }
});

Alpine.start();
