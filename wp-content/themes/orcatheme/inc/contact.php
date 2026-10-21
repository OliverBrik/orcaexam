<?php
/** Contact form validation, private inbox storage and notifications. */
defined('ABSPATH') || exit;

/** Shared service names for form options, validation and inbox/email content. */
function orca_contact_services($type = 'quote') {
    if ('support' === $type) {
        return array(
            'technical' => orca_text('Teknisk problem', 'Technical issue'),
            'content' => orca_text('Rettelse af indhold', 'Content update'),
            'access' => orca_text('Login eller adgang', 'Login or access'),
            'billing' => orca_text('Faktura eller abonnement', 'Invoice or subscription'),
            'other' => orca_text('Andet', 'Other'),
        );
    }
    return array(
        'website' => orca_text('Hjemmeside eller webshop', 'Website or online store'),
        'branding' => orca_text('Branding og visuel identitet', 'Branding and visual identity'),
        'marketing' => orca_text('Reklame og kampagner', 'Advertising and campaigns'),
        'social-media' => orca_text('Sociale medier', 'Social media'),
        'seo' => orca_text('SEO og online synlighed', 'SEO and online visibility'),
        'complete' => orca_text('En samlet digital løsning', 'A complete digital solution'),
        'other' => orca_text('Andet', 'Other'),
    );
}

function orca_contact_service_options($type = 'quote', $selected = '') {
    $placeholder = 'support' === $type
        ? orca_text('Vælg emne', 'Choose a topic')
        : orca_text('Vælg en service', 'Choose a service');
    echo '<option value="">' . esc_html($placeholder) . '</option>';
    foreach (orca_contact_services($type) as $value => $label) {
        printf('<option value="%s"%s>%s</option>', esc_attr($value), selected($selected, $value, false), esc_html($label));
    }
}

/* Save contact submissions in the private inbox, then send an email notification. */

function orca_handle_contact_form() {
    if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '')) {
        wp_die('Method not allowed.', '', array('response' => 405));
    }
    $referer      = wp_get_referer();
    $redirect_url = $referer ? remove_query_arg(array('contact-status', 'contact-type'), $referer) : home_url('/');
    $type         = isset($_POST['contact_type']) ? sanitize_key(wp_unslash($_POST['contact_type'])) : 'quote';
    $type         = in_array($type, array('quote', 'support'), true) ? $type : 'quote';

    if (! orca_verify_form_nonce($_POST['orca_contact_nonce'] ?? '', 'orca_contact_form')) {
        wp_safe_redirect(add_query_arg(array('contact-status' => 'error', 'contact-type' => $type), $redirect_url));
        exit;
    }

    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';
    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $service = isset($_POST['service']) ? sanitize_key(wp_unslash($_POST['service'])) : '';
    $consent = isset($_POST['consent']) && '1' === $_POST['consent'];

    $allowed_services = array_keys(orca_contact_services($type));
    if (empty($name) || empty($company) || ! is_email($email) || empty($message) || ! in_array($service, $allowed_services, true) || ! $consent) {
        wp_safe_redirect(add_query_arg(array('contact-status' => 'error', 'contact-type' => $type), $redirect_url));
        exit;
    }

    $labels = orca_contact_services($type);
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
    $inquiry_id = wp_insert_post(wp_slash(array(
        'post_type' => 'orca_inquiry',
        'post_status' => 'private',
        'post_title' => $subject,
        'post_content' => implode("\n", $body),
        'meta_input' => array(
            '_orca_type' => $type,
            '_orca_email' => $email,
            '_orca_consent' => '1',
        ),
    )), true);

    if (is_wp_error($inquiry_id) || ! $inquiry_id) {
        wp_safe_redirect(add_query_arg(array('contact-status' => 'error', 'contact-type' => $type), $redirect_url));
        exit;
    }

    $sent   = wp_mail(get_option('admin_email'), $subject, implode("\n", $body), array('Reply-To: ' . $email));
    update_post_meta($inquiry_id, '_orca_mail_sent', $sent ? '1' : '0');

    wp_safe_redirect(add_query_arg(array('contact-status' => 'success', 'contact-type' => $type), $redirect_url));
    exit;
}
add_action('admin_post_orca_submit_contact', 'orca_handle_contact_form');
add_action('admin_post_nopriv_orca_submit_contact', 'orca_handle_contact_form');
