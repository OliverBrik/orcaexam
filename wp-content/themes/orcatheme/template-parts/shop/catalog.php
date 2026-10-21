<?php
/** Shop view: catalog. */
defined('ABSPATH') || exit;
?>
    <section class="orca-shop__catalog" id="orca-products" aria-label="<?php echo esc_attr(orca_text('Produkter', 'Products')); ?>">
        <p class="orca-kicker"><?php echo esc_html(orca_text('Find det rette til jeres virksomhed', 'Find the right fit for your business')); ?></p>
        <div class="orca-shop__catalog-heading"><h2><?php echo esc_html(orca_text('Profilprodukter og tryksager', 'Branded merchandise and print')); ?></h2><a href="<?php echo esc_url(wc_get_cart_url()); ?>"><?php echo esc_html(orca_text('Se din kurv', 'View your basket')); ?> <span aria-hidden="true">↗</span></a></div>
        <p class="orca-shop__custom-note"><?php echo esc_html(orca_text('Vil I have et produkt med jeres logo? Send en forespørgsel fra produktsiden, så vi kan afklare antal, tilpasning og pris.', 'Want a product featuring your logo? Send an enquiry from the product page so we can discuss quantity, customisation and pricing.')); ?></p>
        <div class="orca-shop__tools">
            <nav class="orca-shop__categories" aria-label="<?php echo esc_attr(orca_text('Produktkategorier', 'Product categories')); ?>">
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" <?php if (is_shop()) { echo 'aria-current="page"'; } ?>><?php echo esc_html(orca_text('Alle produkter', 'All products')); ?></a>
                <?php
                $categories = get_terms(array('taxonomy'=>'product_cat', 'hide_empty'=>true, 'parent'=>0));
                if (!is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $link = get_term_link($category);
                        if (is_wp_error($link)) { continue; }
                        printf('<a href="%s"%s>%s</a>', esc_url($link), is_product_category($category->term_id) ? ' aria-current="page"' : '', esc_html($category->name));
                    }
                }
                ?>
            </nav>
            <form role="search" method="get" class="orca-shop__search" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="screen-reader-text" for="orca-product-search"><?php echo esc_html(orca_text('Søg efter produkter', 'Search products')); ?></label>
                <input type="search" id="orca-product-search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php echo esc_attr(orca_text('Søg efter produkter…', 'Search products…')); ?>">
                <input type="hidden" name="post_type" value="product">
                <button type="submit"><?php echo esc_html(orca_text('Søg', 'Search')); ?></button>
            </form>
        </div>
    </section>
    <?php
    do_action('woocommerce_archive_description');
