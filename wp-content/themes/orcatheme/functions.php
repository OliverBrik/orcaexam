<?php

function orca_theme_setup() {
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'orca_theme_setup');

function orca_theme_styles() {
    wp_enqueue_style(
        'orca-theme-style',
        get_stylesheet_uri()
    );
}
add_action('wp_enqueue_scripts', 'orca_theme_styles');

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
