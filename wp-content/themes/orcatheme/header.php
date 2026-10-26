<!doctype html>
<html lang="da">
<head>
    <meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container header-inner">
        <div class="logo">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <span class="brand-name"><?php echo esc_html(get_bloginfo('name')); ?></span>
                <span class="tagline"><?php echo esc_html(get_bloginfo('description')); ?></span>
            </a>
        </div>

        <nav class="main-nav" aria-label="<?php echo esc_attr('Primær navigation'); ?>">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container'      => false,
                'fallback_cb'    => false,
                'menu_class'     => 'nav-menu',
            ));
            ?>
        </nav>

        <div class="header-actions">
            <?php if (function_exists('wc_get_page_permalink')) : ?>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="header-button"><?php echo esc_html('Webshop'); ?></a>
            <?php endif; ?>
            <a href="<?php echo esc_url(home_url('/blog')); ?>" class="header-button"><?php echo esc_html('Blog'); ?></a>
            <a href="<?php echo esc_url(home_url('/leave-a-review')); ?>" class="header-button"><?php echo esc_html('Skriv en anmeldelse'); ?></a>
            <a href="<?php echo esc_url(home_url('/sustainability-initiatives')); ?>" class="header-button"><?php echo esc_html('Bæredygtighed'); ?></a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="header-button"><?php echo esc_html('Kontakt os'); ?></a>
            <?php if (function_exists('wc_get_page_permalink')) : ?>
                <?php echo orca_cart_link_markup(); ?>
            <?php endif; ?>
        </div>
    </div>
</header>
