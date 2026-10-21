(function () {
    document.querySelectorAll('.orca-actions form').forEach(function (form) {
        const button = form.querySelector('[data-orca-like]');
        const count = form.closest('.orca-actions').querySelector('.orca-like-count');
        if (!button || !count) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            button.disabled = true;
            const formData = new FormData(form);
            formData.set('orca_blog_action', button.classList.contains('orca-liked') ? 'unlike' : 'like');

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (response) {
                    if (!response.success || !response.data || typeof response.data.liked !== 'boolean') {
                        throw new Error('Like could not be saved.');
                    }

                    const likes = Number(response.data.likes);
                    button.textContent = response.data.liked ? orcaBlogLabels.liked : orcaBlogLabels.like;
                    button.classList.toggle('orca-liked', response.data.liked);
                    button.disabled = false;
                    count.textContent = likes + ' ' + (1 === likes ? orcaBlogLabels.likeCount : orcaBlogLabels.likesCount);
                })
                .catch(function () {
                    button.disabled = false;
                });
        });
    });

})();

(function () {
    const filterPanel = document.querySelector('.orca-filter');
    const filterForm = document.querySelector('.orca-filter form');
    if (!filterPanel || !filterForm) {
        return;
    }

    const sortButtons = filterForm.querySelectorAll('[data-sort-desc]');
    const sortInput = filterForm.querySelector('input[name="blog_sort"]');

    sortButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!sortInput) {
                return;
            }

            const arrow = button.querySelector('.orca-sort-arrow');
            const currentState = arrow ? arrow.textContent.trim() : '';
            const selectedSort = '↓' === currentState
                ? button.getAttribute('data-sort-asc')
                : '↑' === currentState
                    ? ''
                    : button.getAttribute('data-sort-desc');

            sortButtons.forEach(function (item) {
                const itemArrow = item.querySelector('.orca-sort-arrow');
                const isSelected = item === button && selectedSort;
                item.classList.toggle('orca-active-sort', Boolean(isSelected));
                if (itemArrow) {
                    itemArrow.textContent = isSelected ? (selectedSort === item.getAttribute('data-sort-desc') ? '↓' : '↑') : '';
                }
            });
            sortInput.value = selectedSort || '';
        });
    });

    document.addEventListener('click', function (event) {
        if (filterPanel.open && !filterPanel.contains(event.target)) {
            filterPanel.removeAttribute('open');
        }
    });
})();
