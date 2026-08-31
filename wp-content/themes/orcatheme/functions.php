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
        'show_in_rest' => false,
        'menu_position' => 20,
    ));
}
add_action('init', 'orca_register_testimonials');

function orca_disable_gutenberg_for_testimonials($can_edit, $post_type) {
    if ($post_type === 'testimonial') {
        return false;
    }

    return $can_edit;
}
add_filter('use_block_editor_for_post_type', 'orca_disable_gutenberg_for_testimonials', 10, 2);
add_filter('gutenberg_can_edit_post_type', 'orca_disable_gutenberg_for_testimonials', 10, 2);

function orca_testimonial_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['testimonial_submit'])) {
        $name = sanitize_text_field($_POST['testimonial_name']);
        $review = sanitize_textarea_field($_POST['testimonial_review']);

        if ($name && $review) {
            $post_id = wp_insert_post(array(
                'post_title'   => $name,
                'post_content' => $review,
                'post_type'    => 'testimonial',
                'post_status'  => 'pending',
            ));

            if ($post_id) {
                echo '<p>Thank you! Your review has been submitted for approval.</p>';
            } else {
                echo '<p>There was a problem submitting your review.</p>';
            }
        } else {
            echo '<p>Please enter your name and review.</p>';
        }
    }

    ob_start();
    ?>
    <form method="post" style="max-width:600px; margin-top:30px;">
        <p>
            <label for="testimonial_name">Your Name</label><br>
            <input type="text" name="testimonial_name" id="testimonial_name" required style="width:100%; padding:10px;">
        </p>

        <p>
            <label for="testimonial_review">Your Review</label><br>
            <textarea name="testimonial_review" id="testimonial_review" rows="5" required style="width:100%; padding:10px;"></textarea>
        </p>

        <p>
            <button type="submit" name="testimonial_submit">Send Review</button>
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('testimonial_form', 'orca_testimonial_form');

function orca_testimonial_admin_row_actions($actions, $post) {
    if ($post->post_type !== 'testimonial') {
        return $actions;
    }

    if ($post->post_status !== 'publish') {
        $actions['approve_testimonial'] = '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=orca_approve_testimonial&post_id=' . $post->ID), 'orca_approve_testimonial_' . $post->ID) . '">Approve</a>';
    } else {
        $actions['decline_testimonial'] = '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=orca_decline_testimonial&post_id=' . $post->ID), 'orca_decline_testimonial_' . $post->ID) . '">Decline</a>';
    }

    return $actions;
}
add_filter('post_row_actions', 'orca_testimonial_admin_row_actions', 10, 2);

function orca_approve_testimonial_request() {
    if (!isset($_GET['post_id'])) {
        return;
    }

    $post_id = absint($_GET['post_id']);

    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_die('You are not allowed to do that.');
    }

    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'orca_approve_testimonial_' . $post_id)) {
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

function orca_decline_testimonial_request() {
    if (!isset($_GET['post_id'])) {
        return;
    }

    $post_id = absint($_GET['post_id']);

    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_die('You are not allowed to do that.');
    }

    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'orca_decline_testimonial_' . $post_id)) {
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
