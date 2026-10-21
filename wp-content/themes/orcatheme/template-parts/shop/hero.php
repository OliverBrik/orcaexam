<?php
/** Shop view: hero. */
defined('ABSPATH') || exit;
?>
    <section class="orca-hero orca-shop__hero" aria-labelledby="orca-shop-title">
            <div class="orca-eyebrow"><?php echo esc_html(orca_text('Jeres brand i hverdagen', 'Your brand, every day')); ?></div>
            <h1 id="orca-shop-title"><?php if (is_shop()) : ?><?php echo esc_html(orca_text('Jeres logo.', 'Your logo.')); ?><br><span><?php echo esc_html(orca_text('En del af hverdagen.', 'Part of everyday life.')); ?></span><?php else : ?><?php echo esc_html(woocommerce_page_title(false)); ?><?php endif; ?></h1>
            <p class="orca-shop__intro"><?php echo esc_html(orca_text('Caps, trøjer, kuglepenne og visitkort med jeres virksomhedslogo. Gør jer synlige i hverdagen, til kundemøder og på messer — og få vores hjælp til logo, design og et samlet visuelt udtryk.', 'Caps, shirts, pens and business cards featuring your company logo. Get noticed every day, at client meetings and trade shows — with our help on your logo, design and visual identity.')); ?></p>
            <div class="orca-shop__hero-actions">
                <a class="orca-shop__browse" href="#orca-products"><?php echo esc_html(orca_text('Se produkterne', 'Explore products')); ?> <span aria-hidden="true">↘</span></a>
                <a class="orca-shop__service-link" href="<?php echo esc_url(orca_shop_enquiry_url('branding')); ?>"><?php echo esc_html(orca_text('Brug for hjælp til jeres logo?', 'Need help with your logo?')); ?> ↗</a>
            </div>
            <div class="orca-contact__facts orca-shop__facts"><span><?php echo esc_html(orca_text('Produkter med jeres logo', 'Products with your logo')); ?></span><span><?php echo esc_html(orca_text('Hjælp til design og branding', 'Design and branding assistance')); ?></span><span><?php echo esc_html(orca_text('Tilpasset jeres virksomhed', 'Tailored to your business')); ?></span></div>
    </section>
    <?php
