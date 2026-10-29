<?php
/** Theme support and versioned frontend assets. */
defined('ABSPATH') || exit;

function orca_theme_setup() {
    add_theme_support('title-tag');
    register_nav_menus(array('primary' => 'Primary Menu'));
}
add_action('after_setup_theme', 'orca_theme_setup');

function orca_danish_menu_title($title) {
    $translations = array(
        'Home' => 'Forside',
        'Reviews' => 'Anmeldelser',
        'Contact' => 'Kontakt',
        'About us' => 'Om os',
    );

    return isset($translations[$title]) ? $translations[$title] : $title;
}
add_filter('nav_menu_item_title', 'orca_danish_menu_title');

function orca_asset_version($relative_path) {
    $file = get_theme_file_path($relative_path);
    return is_file($file) ? (string) filemtime($file) : wp_get_theme()->get('Version');
}

function orca_theme_assets() {
    wp_enqueue_style('orca-fonts', get_theme_file_uri('/assets/css/fonts.css'), array(), orca_asset_version('/assets/css/fonts.css'));
    wp_enqueue_style('orca-theme-style', get_theme_file_uri('/assets/css/theme.css'), array('orca-fonts'), orca_asset_version('/assets/css/theme.css'));

    $scripts = array(
        'home' => is_front_page(),
        'contact' => is_page_template('contact.php'),
        'blog' => is_page_template('blog.php'),
    );
    foreach ($scripts as $name => $needed) {
        if (!$needed) { continue; }
        $file = '/assets/js/' . $name . '.js';
        wp_enqueue_script('orca-' . $name, get_theme_file_uri($file), array(), orca_asset_version($file), true);
    }
    if ($scripts['blog']) {
        wp_localize_script('orca-blog', 'orcaBlogLabels', array(
            'liked' => '♥ Liket',
            'like' => '♥ Like',
            'likeCount' => 'like',
            'likesCount' => 'likes',
        ));
    }
}
add_action('wp_enqueue_scripts', 'orca_theme_assets');
