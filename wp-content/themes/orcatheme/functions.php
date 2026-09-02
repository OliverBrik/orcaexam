<?php
/**Set up the theme: enable the title tag and register the main navigation menu. */

function orca_theme_setup() {
    add_theme_support('title-tag');
    register_nav_menus(array(
        'primary' => 'Primary Menu',
    ));
}
add_action('after_setup_theme', 'orca_theme_setup');

/*Loads the theme stylesheet so the CSS is included.*/
function orca_theme_styles() {
    wp_enqueue_style('orca-theme-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'orca_theme_styles');

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
        $actions['approve_testimonial'] = '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=orca_approve_testimonial&post_id=' . $post->ID), 'orca_approve_testimonial_' . $post->ID) . '">Approve</a>';
    } else {
        $actions['decline_testimonial'] = '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=orca_decline_testimonial&post_id=' . $post->ID), 'orca_decline_testimonial_' . $post->ID) . '">Decline</a>';
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

/* Decline a testimonial by setting it back to draft so it doesn't show publicly. */

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

/* Handle the contact form submission and send the message by email. */

function orca_handle_contact_form() {
    $referer      = wp_get_referer();
    $redirect_url = $referer ? remove_query_arg(array('contact-status', 'contact-type'), $referer) : home_url('/');
    $type         = isset($_POST['contact_type']) ? sanitize_key(wp_unslash($_POST['contact_type'])) : 'quote';
    $type         = in_array($type, array('quote', 'support'), true) ? $type : 'quote';

    if (! isset($_POST['orca_contact_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['orca_contact_nonce'])), 'orca_contact_form')) {
        wp_safe_redirect(add_query_arg(array('contact-status' => 'error', 'contact-type' => $type), $redirect_url));
        exit;
    }

    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';
    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $service = isset($_POST['service']) ? sanitize_key(wp_unslash($_POST['service'])) : '';
    $consent = isset($_POST['consent']) && '1' === $_POST['consent'];

    if (empty($name) || empty($company) || ! is_email($email) || empty($message) || empty($service) || ! $consent) {
        wp_safe_redirect(add_query_arg(array('contact-status' => 'error', 'contact-type' => $type), $redirect_url));
        exit;
    }

    $labels = array('website' => 'Hjemmeside eller webshop', 'branding' => 'Branding og visuel identitet', 'marketing' => 'Reklame og kampagner', 'social-media' => 'Sociale medier', 'seo' => 'SEO og online synlighed', 'complete' => 'En samlet digital løsning', 'technical' => 'Teknisk problem', 'content' => 'Rettelse af indhold', 'access' => 'Login eller adgang', 'billing' => 'Faktura eller abonnement', 'other' => 'Andet');
    $subject = 'support' === $type ? 'Ny supportsag fra ' . $company : 'Ny tilbudsforespørgsel fra ' . $company;
    $body    = array('Type: ' . ('support' === $type ? 'Support' : 'Tilbud'), 'Navn: ' . $name, 'Virksomhed: ' . $company, 'E-mail: ' . $email, 'Emne/service: ' . (isset($labels[$service]) ? $labels[$service] : $service));

    if ('quote' === $type) {
        $body[] = 'Telefon: ' . (isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : 'Ikke angivet');
        $body[] = 'Budget: ' . (isset($_POST['budget']) ? sanitize_text_field(wp_unslash($_POST['budget'])) : 'Ikke angivet');
        $body[] = 'Deadline: ' . (isset($_POST['deadline']) ? sanitize_text_field(wp_unslash($_POST['deadline'])) : 'Ikke angivet');
    } else {
        $body[] = 'Website: ' . (isset($_POST['website']) ? esc_url_raw(wp_unslash($_POST['website'])) : 'Ikke angivet');
    }

    $body[] = "\nBesked:\n" . $message;
    $sent   = wp_mail(get_option('admin_email'), $subject, implode("\n", $body), array('Reply-To: ' . $name . ' <' . $email . '>'));

    wp_safe_redirect(add_query_arg(array('contact-status' => $sent ? 'success' : 'error', 'contact-type' => $type), $redirect_url));
    exit;
}
add_action('admin_post_orca_submit_contact', 'orca_handle_contact_form');
add_action('admin_post_nopriv_orca_submit_contact', 'orca_handle_contact_form');
