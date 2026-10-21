<?php
/** Theme bootstrap. Keep feature implementations in inc/. */
defined('ABSPATH') || exit;

foreach (array('language', 'security', 'setup', 'testimonials', 'contact-inbox', 'contact', 'woocommerce') as $module) {
    require_once get_theme_file_path('/inc/' . $module . '.php');
}
