// Run with node tests/cart-link.test.cjs; no browser, server or database needed.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../assets/js/cart-link.js'), 'utf8');

function session(managedByWooCommerce = false, blockStore = null) {
    const events = {};
    const requests = [];
    const label = { textContent: '' };
    let container = { hidden: true, replaceWith(value) { container = value; }, querySelector() { return label; } };
    const document = {
        cookie: '', readyState: 'loading',
        querySelector() { return container; },
        addEventListener(name, callback) { events[name] = callback; },
        createElement() {
            return { content: { querySelector() { return { hidden: false, replaceWith(value) { container = value; } }; } } };
        },
    };
    const context = {
        document,
        window: {
            addEventListener(name, callback) { events[name] = callback; },
            wp: blockStore ? { data: { select() { return blockStore; }, subscribe(callback) { events.storeChange = callback; } } } : undefined,
        },
        orcaCartLink: { endpoint: '/?wc-ajax=get_refreshed_fragments', managedByWooCommerce, usesCartStore: !!blockStore, itemSingular: 'vare', itemPlural: 'varer' },
        fetch(url, options) {
            return new Promise((resolve, reject) => requests.push({ url, options, resolve, reject }));
        },
    };
    vm.runInNewContext(source, context);
    return { document, events, requests, label, get container() { return container; } };
}

async function respond(request) {
    request.resolve({ ok: true, json: async () => ({ fragments: { '.orca-cart-link-container': '<span>Kurv (1 vare)</span>' } }) });
    await new Promise(resolve => setImmediate(resolve));
}

(async () => {
    const guest = session();
    guest.events.pageshow();
    assert.equal(guest.requests.length, 0, 'A new visitor must not cause a fragments request');
    guest.document.cookie = 'other=cookie; woocommerce_items_in_cart=1';
    guest.events.pageshow();
    assert.equal(guest.requests.length, 1);
    assert.equal(guest.requests[0].options.method, 'POST');
    await respond(guest.requests[0]);
    assert.equal(guest.container.hidden, false, 'Existing carts must become visible');
    guest.document.cookie = '';
    guest.events.pageshow();
    assert.equal(guest.container.hidden, true, 'An emptied cart must hide on history restoration');
    assert.equal(guest.requests.length, 1, 'An empty cart must not fetch');

    const racing = session();
    racing.document.cookie = 'woocommerce_items_in_cart=1';
    racing.events.pageshow();
    racing.document.cookie = '';
    await respond(racing.requests[0]);
    assert.equal(racing.container.hidden, true, 'A stale response must not resurrect an emptied cart');

    const storage = session();
    storage.document.cookie = 'woocommerce_items_in_cart=1';
    storage.events.storage({ key: 'unrelated' });
    assert.equal(storage.requests.length, 0);
    storage.events.storage({ key: 'wc_cart_hash_example' });
    assert.equal(storage.requests.length, 1, 'Another cart tab must refresh the count');
    await respond(storage.requests[0]);

    const shop = session(true);
    assert.equal(shop.events.pageshow, undefined, 'WooCommerce pages must not get a duplicate startup request');
    shop.document.cookie = 'woocommerce_items_in_cart=1';
    shop.events['wc-blocks_added_to_cart']();
    assert.equal(shop.requests.length, 1, 'Block cart events must refresh the header');
    await respond(shop.requests[0]);
    shop.document.cookie = '';
    shop.events['wc-blocks_removed_from_cart']();
    assert.equal(shop.container.hidden, true);
    let itemsCount = 1;
    const block = session(true, { getCartData() { return { itemsCount }; }, hasFinishedResolution() { return true; } });
    assert.equal(block.label.textContent, '(1 vare)');
    itemsCount = 2;
    block.events.storeChange();
    assert.equal(block.label.textContent, '(2 varer)');
    itemsCount = 0;
    block.events.storeChange();
    assert.equal(block.container.hidden, true, 'Removing the last block-cart item must hide the header count');
    assert.equal(block.requests.length, 0, 'The block cart must reuse its store without extra HTTP requests');
    console.log('Cart link regression checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
