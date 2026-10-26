<?php
/** Shop view: hero. */
defined('ABSPATH') || exit;
?>
    <section class="orca-hero orca-shop__hero" aria-labelledby="orca-shop-title">
            <div class="orca-eyebrow"><?php echo esc_html('Jeres brand i hverdagen'); ?></div>
            <h1 id="orca-shop-title"><?php if (is_shop()) : ?><?php echo esc_html('Jeres logo.'); ?><br><span><?php echo esc_html('En del af hverdagen.'); ?></span><?php else : ?><?php echo esc_html(woocommerce_page_title(false)); ?><?php endif; ?></h1>
            <p class="orca-shop__intro"><?php echo esc_html('Caps, trøjer, kuglepenne og visitkort med jeres virksomhedslogo. Gør jer synlige i hverdagen, til kundemøder og på messer — og få vores hjælp til logo, design og et samlet visuelt udtryk.'); ?></p>
            <div class="orca-shop__hero-actions">
                <a class="orca-shop__browse" href="#orca-products"><?php echo esc_html('Se produkterne'); ?> <span aria-hidden="true">↘</span></a>
                <a class="orca-shop__service-link" href="<?php echo esc_url(orca_shop_enquiry_url('branding')); ?>"><?php echo esc_html('Brug for hjælp til jeres logo?'); ?> ↗</a>
            </div>
            <div class="orca-contact__facts orca-shop__facts"><span><?php echo esc_html('Produkter med jeres logo'); ?></span><span><?php echo esc_html('Hjælp til design og branding'); ?></span><span><?php echo esc_html('Tilpasset jeres virksomhed'); ?></span></div>
    </section>
    <?php
