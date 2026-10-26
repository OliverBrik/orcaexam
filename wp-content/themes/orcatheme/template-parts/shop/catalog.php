<?php
/** Shop view: catalog. */
defined('ABSPATH') || exit;
?>
    <section class="orca-shop__catalog" id="orca-products" aria-label="<?php echo esc_attr('Produkter'); ?>">
        <p class="orca-kicker"><?php echo esc_html('Find det rette til jeres virksomhed'); ?></p>
        <div class="orca-shop__catalog-heading"><h2><?php echo esc_html('Profilprodukter og tryksager'); ?></h2><a href="<?php echo esc_url(wc_get_cart_url()); ?>"><?php echo esc_html('Se din kurv'); ?> <span aria-hidden="true">↗</span></a></div>
        <p class="orca-shop__custom-note"><?php echo esc_html('Vil I have et produkt med jeres logo? Send en forespørgsel fra produktsiden, så vi kan afklare antal, tilpasning og pris.'); ?></p>
        <div class="orca-shop__tools">
            <nav class="orca-shop__categories" aria-label="<?php echo esc_attr('Produktkategorier'); ?>">
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" <?php if (is_shop()) { echo 'aria-current="page"'; } ?>><?php echo esc_html('Alle produkter'); ?></a>
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
                <label class="screen-reader-text" for="orca-product-search"><?php echo esc_html('Søg efter produkter'); ?></label>
                <input type="search" id="orca-product-search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php echo esc_attr('Søg efter produkter…'); ?>">
                <input type="hidden" name="post_type" value="product">
                <button type="submit"><?php echo esc_html('Søg'); ?></button>
            </form>
        </div>
    </section>
    <?php
    do_action('woocommerce_archive_description');
