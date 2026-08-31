<?php

function orca_theme_styles() {
    wp_enqueue_style(
        'orca-theme-style',
        get_stylesheet_uri()
    );
}

// Test comment for GitHub  commit //


add_action('wp_enqueue_scripts', 'orca_theme_styles');