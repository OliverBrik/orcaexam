/* Only visitors with items need a live header count on non-shop pages. */
(function () {
    let pending = false;
    let queued = false;

    function hasCartItems() {
        return document.cookie.split(';').some(function (cookie) {
            return /^woocommerce_items_in_cart=[1-9]\d*$/.test(cookie.trim());
        });
    }

    function refresh() {
        const container = document.querySelector('.orca-cart-link-container');
        if (!container || typeof orcaCartLink === 'undefined') { return; }
        if (!hasCartItems()) {
            container.hidden = true;
            return;
        }
        if (pending) { queued = true; return; }
        pending = true;
        fetch(orcaCartLink.endpoint, { method: 'POST', credentials: 'same-origin', cache: 'no-store' })
            .then(function (response) {
                if (!response.ok) { throw new Error('Cart refresh failed'); }
                return response.json();
            })
            .then(function (data) {
                // Another tab may have emptied the cart while this request ran.
                if (!hasCartItems()) {
                    const current = document.querySelector('.orca-cart-link-container');
                    if (current) { current.hidden = true; }
                    return;
                }
                const html = data.fragments && data.fragments['.orca-cart-link-container'];
                if (typeof html !== 'string') { return; }
                const template = document.createElement('template');
                template.innerHTML = html;
                const replacement = template.content.querySelector('.orca-cart-link-container');
                const current = document.querySelector('.orca-cart-link-container');
                if (current && replacement) { current.replaceWith(replacement); }
            })
            .catch(function () {
                // Retain the server-rendered link if a refresh fails.
            })
            .finally(function () {
                pending = false;
                if (queued) { queued = false; refresh(); }
            });
    }

    // The block cart updates its data store directly, including quantity edits.
    // Reuse that data instead of making another HTTP request for the header.
    if (typeof orcaCartLink !== 'undefined' && orcaCartLink.usesCartStore && window.wp && window.wp.data) {
        const syncStore = function () {
            const store = window.wp.data.select('wc/store/cart');
            const cart = store.getCartData();
            if (!store.hasFinishedResolution('getCartData')) { return; }
            const count = cart.itemsCount;
            const container = document.querySelector('.orca-cart-link-container');
            if (!container || !Number.isInteger(count)) { return; }
            const label = container.querySelector('.orca-cart-link__count');
            container.hidden = count < 1;
            if (label) {
                label.textContent = '(' + count + ' ' + (count === 1 ? orcaCartLink.itemSingular : orcaCartLink.itemPlural) + ')';
            }
        };
        window.wp.data.subscribe(syncStore, 'wc/store/cart');
        syncStore();
    } else {
        document.addEventListener('wc-blocks_added_to_cart', refresh);
        document.addEventListener('wc-blocks_removed_from_cart', refresh);
    }
    if (typeof orcaCartLink !== 'undefined' && !orcaCartLink.managedByWooCommerce) {
        // Also runs when a content page is restored from browser history.
        window.addEventListener('pageshow', refresh);
        if (document.readyState === 'complete') { refresh(); }
        window.addEventListener('storage', function (event) {
            if (event.key === null || event.key.indexOf('wc_cart_hash_') === 0) { refresh(); }
        });
    }
})();
