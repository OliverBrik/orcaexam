<?php
/** CLI regression checks, without booting plugins or writing to the database. */
if (PHP_SAPI !== 'cli') { exit; }
define('ABSPATH', dirname(__DIR__, 4) . '/');
define('DAY_IN_SECONDS', 86400);
$hooks = array();
$checks = 0;
$test_user_id = 0;
$test_commerce = false;
$test_cart = false;
$test_post = null;
function add_filter($name, $callback, $priority = 10, $accepted = 1) {
    $GLOBALS['hooks'][$name][$priority][] = array($callback, $accepted);
}
function add_action($name, $callback, $priority = 10, $accepted = 1) { add_filter($name, $callback, $priority, $accepted); }
function apply_filters($name, $value, ...$args) {
    $groups = $GLOBALS['hooks'][$name] ?? array(); ksort($groups);
    foreach ($groups as $callbacks) {
        foreach ($callbacks as $callback) { $value = call_user_func_array($callback[0], array_slice(array_merge(array($value), $args), 0, $callback[1])); }
    }
    return $value;
}
function do_action($name, ...$args) {}
function wp_get_current_user() { return (object) array('ID' => $GLOBALS['test_user_id']); }
function is_user_logged_in() { return $GLOBALS['test_user_id'] > 0; }
function wp_get_session_token() { return ''; }
function wp_salt($scheme = '') { return 'test-only-fixed-salt-not-a-site-credential'; }
function wp_unslash($value) { return stripslashes($value); }
function sanitize_text_field($value) { return trim(strip_tags($value)); }
function is_ssl() { return false; }
function is_woocommerce() { return $GLOBALS['test_commerce']; }
function is_cart() { return $GLOBALS['test_cart']; }
function is_checkout() { return false; }
function is_account_page() { return false; }
function get_queried_object() { return $GLOBALS['test_post']; }
class WP_Post { public $post_content = ''; }
function check($passed, $description) {
    $GLOBALS['checks']++;
    if (!$passed) { throw new RuntimeException($description); }
}
require ABSPATH . 'wp-includes/pluggable.php';
require ABSPATH . 'wp-includes/shortcodes.php';
require dirname(__DIR__) . '/inc/security.php';
require dirname(__DIR__) . '/inc/woocommerce.php';

// Use WordPress's real nonce creation/verification and the theme's signed cookie.
$_SERVER['REQUEST_METHOD'] = 'GET';
orca_guest_form_session();
$cookie = $_COOKIE['orca_form_session'];
$nonce = wp_create_nonce('orca_contact_form');
$_SERVER['REQUEST_METHOD'] = 'POST';
unset($GLOBALS['orca_guest_form_identity']);
check((bool) orca_verify_form_nonce($nonce, 'orca_contact_form'), 'Valid contact nonce must restore guest identity without template_redirect.');
check(orca_verify_form_nonce($nonce, 'orca_other_action') === false, 'Wrong action must fail.');
check(orca_verify_form_nonce('invalid', 'orca_contact_form') === false, 'Invalid token must fail.');
unset($GLOBALS['orca_guest_form_identity'], $_COOKIE['orca_form_session']);
check(orca_verify_form_nonce($nonce, 'orca_contact_form') === false, 'A missing guest cookie must fail.');
check(!isset($_COOKIE['orca_form_session']), 'POST must not issue a replacement guest cookie.');
$_COOKIE['orca_form_session'] = $cookie . 'tampered';
check(orca_verify_form_nonce($nonce, 'orca_contact_form') === false, 'A tampered signed cookie must fail.');
$_SERVER['REQUEST_METHOD'] = 'GET';
unset($_COOKIE['orca_form_session'], $GLOBALS['orca_guest_form_identity']);
orca_guest_form_session();
$_SERVER['REQUEST_METHOD'] = 'POST';
check(orca_verify_form_nonce($nonce, 'orca_contact_form') === false, 'Another visitor must not reuse the token.');
$test_user_id = 7;
$member_nonce = wp_create_nonce('orca_contact_form');
check((bool) orca_verify_form_nonce($member_nonce, 'orca_contact_form'), 'Logged-in tokens must continue to work.');

// Asset policy must preserve embedded commerce, not just /shop/ routes.
check(!orca_needs_commerce_assets(), 'Plain content should not need commerce assets.');
$test_commerce = true;
check(orca_needs_commerce_assets(), 'Commerce routes must keep assets.');
$test_commerce = false;
$test_post = new WP_Post();
add_shortcode('products', static function () {});
$test_post->post_content = '[products limit="3"]';
check(orca_needs_commerce_assets(), 'Embedded product shortcodes must keep assets.');
$test_post->post_content = '<!-- wp:woocommerce/product-collection -->';
check(orca_needs_commerce_assets(), 'WooCommerce blocks must keep assets.');
$test_post->post_content = 'Ordinary contact content';
check(!orca_needs_commerce_assets(), 'Ordinary content must stay light.');
add_filter('orca_needs_commerce_assets', static function () { return true; });
check(orca_needs_commerce_assets(), 'Integrations must be able to opt in to commerce assets.');
check(apply_filters('wpo_minify_run_on_page', true) === true, 'Content page minification must stay enabled.');
$test_cart = true;
check(apply_filters('wpo_minify_run_on_page', true) === false, 'Cart block assets must bypass minification.');
echo $checks . " regression checks passed. No database or mail used.\n";
