<?php
/** Danish/English copy, language cookies and navigation labels. */
defined('ABSPATH') || exit;

/* Handle the visitor-facing Danish/English language choice. */
function orca_get_language() {
    $language = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';

    if (! in_array($language, array('da', 'en'), true)) {
        $language = isset($_COOKIE['orca_language']) ? sanitize_key(wp_unslash($_COOKIE['orca_language'])) : 'da';
    }

    return in_array($language, array('da', 'en'), true) ? $language : 'da';
}

function orca_set_language_cookie() {
    if (! isset($_GET['lang'])) {
        return;
    }

    $language = sanitize_key(wp_unslash($_GET['lang']));

    if (in_array($language, array('da', 'en'), true)) {
        setcookie('orca_language', $language, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
        $_COOKIE['orca_language'] = $language;
    }
}
add_action('init', 'orca_set_language_cookie');

function orca_text($danish, $english) {
    return 'en' === orca_get_language() ? $english : $danish;
}

function orca_language_url($language) {
    return add_query_arg('lang', $language);
}

/* Translate common WordPress menu labels while keeping menu links unchanged. */
function orca_translate_menu_title($title) {
    $menu_titles = array(
        'Home'         => array('Forside', 'Home'),
        'Forside'      => array('Forside', 'Home'),
        'Blog'         => array('Blog', 'Blog'),
        'Reviews'      => array('Anmeldelser', 'Reviews'),
        'Anmeldelser'  => array('Anmeldelser', 'Reviews'),
        'Contact'      => array('Kontakt', 'Contact'),
        'Kontakt'      => array('Kontakt', 'Contact'),
        'About us'     => array('Om os', 'About us'),
        'Om os'        => array('Om os', 'About us'),
    );

    if (! isset($menu_titles[$title])) {
        return $title;
    }

    return 'en' === orca_get_language() ? $menu_titles[$title][1] : $menu_titles[$title][0];
}
add_filter('nav_menu_item_title', 'orca_translate_menu_title');
