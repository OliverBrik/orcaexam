<?php
/** Security for the theme's own forms and visitor sessions. */
if (! defined('ABSPATH')) {
    exit;
}

/** Give anonymous visitors a signed identity instead of sharing nonce user 0. */
function orca_guest_form_session() {
    if (is_user_logged_in()) {
        return;
    }

    $cookie = $_COOKIE['orca_form_session'] ?? '';
    $parts = is_string($cookie) ? explode('.', $cookie) : array();
    $valid = count($parts) === 2 && preg_match('/^[a-f0-9]{64}$/D', $parts[0])
        && hash_equals(hash_hmac('sha256', $parts[0], wp_salt('nonce')), $parts[1]);

    if (! $valid) {
        // Never replace an absent/invalid session during a submission.
        if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? 'GET')) {
            return;
        }
        $identity = bin2hex(random_bytes(32));
        $cookie = $identity . '.' . hash_hmac('sha256', $identity, wp_salt('nonce'));
        setcookie('orca_form_session', $cookie, array(
            'expires' => 0,
            'path' => '/',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ));
        $_COOKIE['orca_form_session'] = $cookie;
        $parts = explode('.', $cookie);
    }

    if ($valid || isset($identity)) {
        $GLOBALS['orca_guest_form_identity'] = $parts[0];
    }
}

function orca_request_needs_guest_form_session() {
    return is_page_template('contact.php')
        || is_page_template('testimonial.php')
        || is_page_template('blog.php')
        || is_single();
}

function orca_prepare_guest_form_session() {
    if (orca_request_needs_guest_form_session()) {
        orca_guest_form_session();
    }
}
add_action('template_redirect', 'orca_prepare_guest_form_session', 1);

function orca_guest_nonce_identity($user_id, $action) {
    if (is_string($action) && strpos($action, 'orca_') === 0) {
        return $GLOBALS['orca_guest_form_identity'] ?? 'orca_missing_session';
    }
    return $user_id;
}
add_filter('nonce_user_logged_out', 'orca_guest_nonce_identity', 10, 2);

/** These fields are text, never arrays; reject malformed requests early. */
function orca_validate_request_shapes() {
    $fields = array(
        'lang', 'contact-status', 'contact-type', 'contact_type', 'name', 'company',
        'email', 'message', 'service', 'consent', 'phone', 'budget', 'deadline',
        'website', 'orca_contact_nonce', 'testimonial_submit', 'testimonial_name',
        'testimonial_review', 'orca_testimonial_nonce', 'orca_blog_action',
        'orca_blog_nonce', 'post_id', 'post_title', 'post_content', 'comment',
        'comment_author', 'comment_email', 'orca_single_comment', 'orca_single_nonce',
        'discussion_status', 'blog_notice', 'blog_category', 'blog_sort', '_wpnonce',
        'shop-service', 'shop-product',
    );
    foreach (array($_GET, $_POST) as $input) {
        foreach ($fields as $field) {
            if (isset($input[$field]) && ! is_string($input[$field])) {
                wp_die('Invalid form data.', '', array('response' => 400));
            }
        }
    }
    if (isset($_COOKIE['orca_language']) && ! is_string($_COOKIE['orca_language'])) {
        unset($_COOKIE['orca_language']);
    }
}
add_action('init', 'orca_validate_request_shapes', 0);

/** A guest token must be accompanied by the visitor session that issued it. */
function orca_verify_form_nonce($nonce, $action) {
    if (! is_string($nonce) || (! is_user_logged_in() && empty($GLOBALS['orca_guest_form_identity']))) {
        return false;
    }
    return wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), $action);
}

/** Session-specific form pages must not be served from a shared page cache. */
function orca_form_cache_headers() {
    if (orca_request_needs_guest_form_session()) {
        if (! defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        nocache_headers();
    }
}
add_action('template_redirect', 'orca_form_cache_headers');

/** Keep post formatting and uploaded video sources, but remove executable HTML. */
function orca_safe_post_content() {
    $allowed = wp_kses_allowed_html('post');
    $allowed['source'] = array('src' => true, 'type' => true, 'media' => true);
    return wp_kses(apply_filters('the_content', get_the_content()), $allowed);
}

/** Navigation titles are plain text, including labels edited in WordPress. */
function orca_safe_menu_title($title) {
    return esc_html($title);
}
add_filter('nav_menu_item_title', 'orca_safe_menu_title', 20);
