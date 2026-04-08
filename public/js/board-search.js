(() => {
    const searchWrappers = document.querySelectorAll('.search_wrap[data-search-url]');

    if (searchWrappers.length === 0) {
        return;
    }

    const buildSearchUrl = (wrapper) => {
        const baseUrl = wrapper.getAttribute('data-search-url');
        if (!baseUrl) {
            return '';
        }

        const params = new URLSearchParams();
        const controls = wrapper.querySelectorAll('select[name], input[name]');

        controls.forEach((control) => {
            if (control.disabled) {
                return;
            }

            const name = control.getAttribute('name');
            if (!name) {
                return;
            }

            if (control.type === 'checkbox' || control.type === 'radio') {
                if (!control.checked) {
                    return;
                }
            }

            const value = (control.value || '').trim();
            if (value !== '') {
                params.set(name, value);
            }
        });

        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    };

    const submitSearch = (wrapper) => {
        const targetUrl = buildSearchUrl(wrapper);
        if (targetUrl) {
            window.location.href = targetUrl;
        }
    };

    searchWrappers.forEach((wrapper) => {
        const submitButton = wrapper.querySelector('[data-search-submit]');
        const keywordInput = wrapper.querySelector('[data-search-keyword]');

        if (submitButton) {
            submitButton.addEventListener('click', () => submitSearch(wrapper));
        }

        if (keywordInput) {
            keywordInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    submitSearch(wrapper);
                }
            });
        }
    });
})();
