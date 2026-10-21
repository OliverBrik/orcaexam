<?php
defined('ABSPATH') || exit;

function orca_is_commerce_page() {
    return function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page());
}

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
    if ('en' === orca_get_language()) { $args['lang'] = 'en'; }
    return add_query_arg($args, home_url('/contact/'));
}

add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product instanceof WC_Product) { return; }
    ?>
    <aside class="orca-shop__personalise">
        <h2><?php echo esc_html(orca_text('Gør produktet til jeres eget', 'Make this product your own')); ?></h2>
        <p><?php echo esc_html(orca_text('Fortæl os om jeres logo, ønsket antal og hvor produktet skal bruges. Vi hjælper med at afklare design og tilpasning, før I bestiller produkter med logo.', 'Tell us about your logo, quantity and how you will use the product. We help clarify the design and customisation before you order branded products.')); ?></p>
        <a class="button" href="<?php echo esc_url(orca_shop_enquiry_url('branding', $product->get_id())); ?>"><?php echo esc_html(orca_text('Få tilbud med jeres logo', 'Request a branded product quote')); ?> ↗</a>
    </aside>
    <?php
}, 25);

function orca_shop_services() {
    get_template_part('template-parts/shop/services');
}

// Cart, checkout and account pages need their saved WooCommerce blocks/content.
add_filter('template_include', function ($template) {
    if (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) {
        return get_theme_file_path('/shop-page.php');
    }
    return $template;
}, 99);
