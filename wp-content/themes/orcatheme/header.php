<!doctype html>
<html <?php language_attributes(); ?>>
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

        <nav class="main-nav" aria-label="Main navigation">
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
            <a href="<?php echo esc_url(home_url('/leave-a-review')); ?>" class="header-button">Leave a review</a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="header-button">Contact Us</a>
        </div>
    </div>
</header>