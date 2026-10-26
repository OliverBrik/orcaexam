<?php
/**
 * Plugin Name: Orca Local Front Page Cache
 * Description: Local-only WP-Optimize configuration, without changing shared database settings.
 */
defined('ABSPATH') || exit;
if (wp_get_environment_type() !== 'local') { return; }

add_filter('option_wpo_cache_config', 'orca_local_cache_config');
add_filter('default_option_wpo_cache_config', 'orca_local_cache_config');
function orca_local_cache_config($config) {
    $config = is_array($config) ? $config : array();
    return array_merge($config, array(
        'enable_page_caching' => true,
        'page_cache_length_value' => 1,
        'page_cache_length_unit' => 'hours',
        'page_cache_length' => 3600,
        'cache_specific_urls_only' => false,
        'cache_include_urls' => array(),
        'enable_user_caching' => false,
        'enable_schedule_preload' => false,
        'enable_sitemap_preload' => false,
        // Shopping sessions always use live HTML.
        'cache_exception_cookies' => array('woocommerce_', 'wp_woocommerce_session_'),
        'cache_ignore_query_variables' => array(),
    ));
}
// The database is shared with hosting; keep these overrides on this machine.
add_filter('pre_update_option_wpo_cache_config', function ($value, $old_value) {
    return $old_value;
}, 10, 2);
