<?php

function orca_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script'));
}
add_action('after_setup_theme', 'orca_theme_setup');

function orca_theme_styles() {
    wp_enqueue_style(
        'orca-theme-style',
        get_stylesheet_uri()
    );
}
add_action('wp_enqueue_scripts', 'orca_theme_styles');

/**
 * Handles an individual like using post meta. A short-lived cookie prevents
 * repeated likes from the same browser without requiring visitors to sign in.
 */
function orca_toggle_like() {
    check_ajax_referer('orca_like_post', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    if (!$post_id || get_post_status($post_id) !== 'publish') {
        wp_send_json_error(array('message' => __('This post is not available.', 'orcatheme')), 404);
    }

    $cookie_name = 'orca_liked_' . $post_id;
    $liked = !empty($_COOKIE[$cookie_name]);
    $count = (int) get_post_meta($post_id, '_orca_likes', true);

    if ($liked) {
        $count = max(0, $count - 1);
        setcookie($cookie_name, '', time() - HOUR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
    } else {
        $count++;
        setcookie($cookie_name, '1', time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
    }

    update_post_meta($post_id, '_orca_likes', $count);
    wp_send_json_success(array('likes' => $count, 'liked' => !$liked));
}
add_action('wp_ajax_orca_toggle_like', 'orca_toggle_like');
add_action('wp_ajax_nopriv_orca_toggle_like', 'orca_toggle_like');

function orca_like_script() {
    wp_register_script('orca-likes', false, array(), '1.0', true);
    wp_enqueue_script('orca-likes');
    wp_add_inline_script('orca-likes', 'document.addEventListener("click", function (event) { const button = event.target.closest(".orca-like-button"); if (!button || button.disabled) return; event.preventDefault(); button.disabled = true; const data = new FormData(); data.append("action", "orca_toggle_like"); data.append("nonce", OrcaLikes.nonce); data.append("post_id", button.dataset.postId); fetch(OrcaLikes.ajaxUrl, { method: "POST", credentials: "same-origin", body: data }).then(r => r.json()).then(r => { if (!r.success) throw new Error(); button.classList.toggle("is-liked", r.data.liked); button.setAttribute("aria-pressed", r.data.liked ? "true" : "false"); button.querySelector(".orca-like-count").textContent = r.data.likes; }).catch(() => { alert("Unable to save your like. Please try again."); }).finally(() => { button.disabled = false; }); });', 'after');
    wp_localize_script('orca-likes', 'OrcaLikes', array('ajaxUrl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('orca_like_post')));
}
add_action('wp_enqueue_scripts', 'orca_like_script');

function orca_like_button($post_id = 0) {
    $post_id = $post_id ?: get_the_ID();
    $likes = (int) get_post_meta($post_id, '_orca_likes', true);
    $liked = !empty($_COOKIE['orca_liked_' . $post_id]);
    printf('<button class="orca-like-button %1$s" type="button" data-post-id="%2$d" aria-pressed="%3$s"><span aria-hidden="true">♥</span> <span class="orca-like-count">%4$d</span> <span class="screen-reader-text">%5$s</span></button>', $liked ? 'is-liked' : '', $post_id, $liked ? 'true' : 'false', $likes, esc_html__('Like this post', 'orcatheme'));
}
