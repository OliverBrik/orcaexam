<?php
/** Testimonial post type and administrator approval actions. */
defined('ABSPATH') || exit;

/*Create a custom post type called "testimonial" so reviews are stored separately from normal posts.*/

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
        'show_in_rest' => false,
        'menu_position' => 20,
    ));
}
add_action('init', 'orca_register_testimonials');

/* Disable Gutenberg for testimonial posts */


function orca_disable_gutenberg_for_testimonials($can_edit, $post_type) {
    if ($post_type === 'testimonial') {
        return false;
    }

    return $can_edit;
}
add_filter('use_block_editor_for_post_type', 'orca_disable_gutenberg_for_testimonials', 10, 2);
add_filter('gutenberg_can_edit_post_type', 'orca_disable_gutenberg_for_testimonials', 10, 2);

/* Add approve/decline links to the testimonial list in the WordPress admin.*/

function orca_testimonial_admin_row_actions($actions, $post) {
    if ($post->post_type !== 'testimonial') {
        return $actions;
    }

    if ($post->post_status !== 'publish') {
        $actions['approve_testimonial'] = '<a href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=orca_approve_testimonial&post_id=' . $post->ID), 'orca_approve_testimonial_' . $post->ID)) . '">Approve</a>';
    } else {
        $actions['decline_testimonial'] = '<a href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=orca_decline_testimonial&post_id=' . $post->ID), 'orca_decline_testimonial_' . $post->ID)) . '">Decline</a>';
    }

    return $actions;
}
add_filter('post_row_actions', 'orca_testimonial_admin_row_actions', 10, 2);

/* Approve a testimonial by changing its status from pending to published.*/


function orca_approve_testimonial_request() {
    if (!isset($_GET['post_id'])) {
        return;
    }

    $post_id = absint($_GET['post_id']);

    if (!$post_id || 'testimonial' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id) || !current_user_can('publish_posts')) {
        wp_die('You are not allowed to do that.');
    }

    if (!orca_verify_form_nonce($_GET['_wpnonce'] ?? '', 'orca_approve_testimonial_' . $post_id)) {
        wp_die('Security check failed.');
    }

    wp_update_post(array(
        'ID' => $post_id,
        'post_status' => 'publish',
    ));

    wp_redirect(admin_url('edit.php?post_type=testimonial'));
    exit;
}
add_action('admin_post_orca_approve_testimonial', 'orca_approve_testimonial_request');

/* Decline a testimonial by setting it back to draft so it doesn't show publicly. */

function orca_decline_testimonial_request() {
    if (!isset($_GET['post_id'])) {
        return;
    }

    $post_id = absint($_GET['post_id']);

    if (!$post_id || 'testimonial' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id)) {
        wp_die('You are not allowed to do that.');
    }

    if (!orca_verify_form_nonce($_GET['_wpnonce'] ?? '', 'orca_decline_testimonial_' . $post_id)) {
        wp_die('Security check failed.');
    }

    wp_update_post(array(
        'ID' => $post_id,
        'post_status' => 'draft',
    ));

    wp_redirect(admin_url('edit.php?post_type=testimonial'));
    exit;
}
add_action('admin_post_orca_decline_testimonial', 'orca_decline_testimonial_request');
