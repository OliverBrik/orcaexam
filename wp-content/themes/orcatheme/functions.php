<?php

function orca_theme_setup() {
    add_theme_support('title-tag');
    register_nav_menus(array(
        'primary' => 'Primary Menu',
    ));
}
add_action('after_setup_theme', 'orca_theme_setup');

function orca_theme_styles() {
    wp_enqueue_style('orca-theme-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'orca_theme_styles');

function orca_register_testimonials() {
    register_post_type('testimonial', array(
        'labels' => array(
            'name' => 'Testimonials',
            'singular_name' => 'Testimonial',
        ),
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-format-quote',
        'supports' => array('title', 'editor'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'orca_register_testimonials');