<?php
/*
Template Name: Submit Testimonial
*/
get_header();
?>

<main class="testimonial-page">
    <?php
    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['testimonial_submit'])) {
        if (!isset($_POST['orca_testimonial_nonce']) || !orca_verify_form_nonce($_POST['orca_testimonial_nonce'], 'orca_submit_testimonial')) {
            $message = 'Sikkerhedskontrollen mislykkedes.';
        } else {
            $name = sanitize_text_field(wp_unslash($_POST['testimonial_name'] ?? ''));
            $review = sanitize_textarea_field(wp_unslash($_POST['testimonial_review'] ?? ''));

            if ($name && $review) {
                $post_id = wp_insert_post(wp_slash(array(
                    'post_title'   => $name,
                    'post_content' => $review,
                    'post_type'    => 'testimonial',
                    'post_status'  => 'pending',
                )), true);

                if ($post_id && ! is_wp_error($post_id)) {
                    $message = 'Tak! Din anmeldelse er sendt til godkendelse.';
                } else {
                    $message = 'Der opstod et problem med at sende din anmeldelse.';
                }
            } else {
                $message = 'Indtast dit navn og din anmeldelse.';
            }
        }
    }
    ?>

    <section class="testimonial-form">
        <h2><?php echo esc_html('Skriv en anmeldelse'); ?></h2>

        <?php if ($message) : ?>
            <p><?php echo esc_html($message); ?></p>
        <?php endif; ?>

        <form class="testimonial-entry-form" method="post">
            <?php wp_nonce_field('orca_submit_testimonial', 'orca_testimonial_nonce'); ?>

            <p>
                <label for="testimonial_name"><?php echo esc_html('Dit navn'); ?></label><br>
                <input type="text" name="testimonial_name" id="testimonial_name" value="" required>
            </p>

            <p>
                <label for="testimonial_review"><?php echo esc_html('Din anmeldelse'); ?></label><br>
                <textarea name="testimonial_review" id="testimonial_review" rows="5" required></textarea>
            </p>

            <p>
                <button class="testimonial-entry-form__submit" type="submit" name="testimonial_submit"><?php echo esc_html('Send anmeldelse'); ?></button>
            </p>
        </form>
    </section>
</main>

<?php get_footer(); ?>
