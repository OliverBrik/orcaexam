<!doctype html>
<html lang="<?php echo esc_attr(orca_get_language()); ?>">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header class="site-header">
    <div class="container header-inner">
        <div class="logo">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <span class="brand-name"><?php bloginfo('name'); ?></span>
                <span class="tagline"><?php bloginfo('description'); ?></span>
            </a>
        </div>

        <nav class="main-nav" aria-label="<?php echo esc_attr(orca_text('Primær navigation', 'Main navigation')); ?>">
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
            <a href="<?php echo esc_url(home_url('/blog')); ?>" class="header-button"><?php echo esc_html(orca_text('Blog', 'Blog')); ?></a>
            <a href="<?php echo esc_url(home_url('/leave-a-review')); ?>" class="header-button"><?php echo esc_html(orca_text('Skriv en anmeldelse', 'Leave a review')); ?></a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="header-button"><?php echo esc_html(orca_text('Kontakt os', 'Contact us')); ?></a>
            <nav class="language-switcher" aria-label="<?php echo esc_attr(orca_text('Vælg sprog', 'Choose language')); ?>">
                <a href="<?php echo esc_url(orca_language_url('da')); ?>" lang="da"<?php echo 'da' === orca_get_language() ? ' aria-current="true"' : ''; ?>>DA</a>
                <span aria-hidden="true">/</span>
                <a href="<?php echo esc_url(orca_language_url('en')); ?>" lang="en"<?php echo 'en' === orca_get_language() ? ' aria-current="true"' : ''; ?>>EN</a>
            </nav>
        </div>
    </div>
</header>
