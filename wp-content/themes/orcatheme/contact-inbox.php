<?php
/** Private, administrator-only inbox for contact form submissions. */

function orca_register_contact_inbox() {
    register_post_type('orca_inquiry', array(
        'labels' => array(
            'name' => 'Henvendelser',
            'singular_name' => 'Henvendelse',
            'edit_item' => 'Læs henvendelse',
            'search_items' => 'Søg i henvendelser',
            'not_found' => 'Ingen henvendelser endnu.',
            'all_items' => 'Alle henvendelser',
        ),
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_rest' => false,
        'rewrite' => false,
        'query_var' => false,
        'menu_icon' => 'dashicons-email-alt',
        'supports' => false,
        'map_meta_cap' => false,
        'capabilities' => array(
            'edit_post' => 'manage_options',
            'read_post' => 'manage_options',
            'delete_post' => 'manage_options',
            'edit_posts' => 'manage_options',
            'edit_others_posts' => 'manage_options',
            'publish_posts' => 'manage_options',
            'read_private_posts' => 'manage_options',
            'delete_posts' => 'manage_options',
            'delete_private_posts' => 'manage_options',
            'delete_published_posts' => 'manage_options',
            'delete_others_posts' => 'manage_options',
            'edit_private_posts' => 'manage_options',
            'edit_published_posts' => 'manage_options',
            'create_posts' => 'do_not_allow',
        ),
    ));
}
add_action('init', 'orca_register_contact_inbox');

function orca_contact_inbox_meta_boxes() {
    remove_meta_box('submitdiv', 'orca_inquiry', 'side');
    add_meta_box('orca-inquiry-message', 'Modtaget henvendelse', 'orca_contact_inbox_message', 'orca_inquiry', 'normal', 'high');
}
add_action('add_meta_boxes_orca_inquiry', 'orca_contact_inbox_meta_boxes');

function orca_contact_inbox_message($post) {
    if (! current_user_can('manage_options')) {
        return;
    }
    echo '<p><strong>' . esc_html($post->post_title) . '</strong></p>';
    echo '<p>' . nl2br(esc_html($post->post_content)) . '</p>';
    $email = get_post_meta($post->ID, '_orca_email', true);
    if (is_email($email)) {
        echo '<p><a class="button button-primary" href="' . esc_url('mailto:' . $email) . '">Svar via e-mail</a></p>';
    }
    echo '<p>E-mailnotifikation: ' . (get_post_meta($post->ID, '_orca_mail_sent', true) ? 'Overdraget til mailsystemet (levering er ikke bekræftet).' : 'Ikke sendt. Henvendelsen er gemt her.') . '</p>';
}

function orca_contact_inbox_columns($columns) {
    return array(
        'cb' => $columns['cb'],
        'title' => 'Henvendelse',
        'orca_type' => 'Type',
        'orca_email' => 'Afsenderens e-mail',
        'date' => 'Modtaget',
    );
}
add_filter('manage_orca_inquiry_posts_columns', 'orca_contact_inbox_columns');

function orca_contact_inbox_column($column, $post_id) {
    if ('orca_type' === $column) {
        echo 'support' === get_post_meta($post_id, '_orca_type', true) ? 'Support' : 'Tilbud';
    } elseif ('orca_email' === $column) {
        echo esc_html(get_post_meta($post_id, '_orca_email', true));
    }
}
add_action('manage_orca_inquiry_posts_custom_column', 'orca_contact_inbox_column', 10, 2);

function orca_contact_inbox_row_actions($actions, $post) {
    if ('orca_inquiry' === $post->post_type) {
        unset($actions['inline hide-if-no-js']);
    }
    return $actions;
}
add_filter('post_row_actions', 'orca_contact_inbox_row_actions', 10, 2);
