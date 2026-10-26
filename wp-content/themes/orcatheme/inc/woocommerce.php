<?php
defined('ABSPATH') || exit;

function orca_is_commerce_page() {
    return function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page());
}

function orca_cart_link_markup() {
    $item_count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $hidden     = $item_count < 1 ? ' hidden' : '';
    $label      = 'Kurv';
    $item_label = 1 === $item_count ? 'vare' : 'varer';

    return sprintf(
        '<span class="orca-cart-link-container"%1$s><a href="%2$s" class="header-button orca-cart-link">%3$s <span class="orca-cart-link__count">(%4$d %5$s)</span></a></span>',
        $hidden,
        esc_url(wc_get_cart_url()),
        esc_html($label),
        $item_count,
        esc_html($item_label)
    );
}

add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $fragments['.orca-cart-link-container'] = orca_cart_link_markup();
    return $fragments;
});

add_action('wp_enqueue_scripts', function () {
    if (function_exists('WC') && ! orca_is_commerce_page()) {
        wp_enqueue_script('wc-cart-fragments');
    }
}, 25);

add_action('after_setup_theme', function () {
    add_theme_support('woocommerce', array('product_grid' => array('default_columns' => 3, 'default_rows' => 4)));
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
});

add_action('wp_enqueue_scripts', function () {
    if (orca_is_commerce_page()) {
        wp_enqueue_style('orca-shop', get_theme_file_uri('/assets/css/shop.css'), array('orca-theme-style'), orca_asset_version('/assets/css/shop.css'));
    }
}, 20);

add_action('wp', function () {
    if (!function_exists('is_woocommerce') || !is_woocommerce()) { return; }
    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    add_action('woocommerce_before_main_content', function () {
        echo '<main class="orca-shop">';
        if (is_shop() || is_product_taxonomy()) { orca_shop_hero(); }
        echo '<div class="container orca-shop__content">';
    }, 10);
    add_action('woocommerce_after_main_content', function () {
        orca_shop_services();
        echo '</div></main>';
    }, 10);
    if (is_shop() || is_product_taxonomy()) {
        remove_action('woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10);
        add_action('woocommerce_shop_loop_header', 'orca_shop_header');
    }
});

function orca_shop_hero() {
    get_template_part('template-parts/shop/hero');
}

function orca_shop_header() {
    get_template_part('template-parts/shop/catalog');
}

function orca_shop_enquiry_url($service = 'branding', $product_id = 0) {
    $args = array('contact-type'=>'quote', 'shop-service'=>$service);
    if ($product_id) { $args['shop-product'] = absint($product_id); }
    return add_query_arg($args, home_url('/contact/'));
}

add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product instanceof WC_Product) { return; }
    ?>
    <aside class="orca-shop__personalise">
        <h2><?php echo esc_html('Gør produktet til jeres eget'); ?></h2>
        <p><?php echo esc_html('Fortæl os om jeres logo, ønsket antal og hvor produktet skal bruges. Vi hjælper med at afklare design og tilpasning, før I bestiller produkter med logo.'); ?></p>
        <a class="button" href="<?php echo esc_url(orca_shop_enquiry_url('branding', $product->get_id())); ?>"><?php echo esc_html('Få tilbud med jeres logo'); ?> ↗</a>
    </aside>
    <?php
}, 25);

function orca_shop_services() {
    get_template_part('template-parts/shop/services');
}

// Kurv-, checkout- og kontosider skal bruge deres gemte WooCommerce-indhold.
add_filter('template_include', function ($template) {
    if (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) {
        return get_theme_file_path('/shop-page.php');
    }
    return $template;
}, 99);
