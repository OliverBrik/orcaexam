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
        if (!isset($_POST['orca_testimonial_nonce']) || !wp_verify_nonce($_POST['orca_testimonial_nonce'], 'orca_submit_testimonial')) {
            $message = orca_text('Sikkerhedskontrollen mislykkedes.', 'Security check failed.');
        } else {
            $name = sanitize_text_field(wp_unslash($_POST['testimonial_name']));
            $review = sanitize_textarea_field(wp_unslash($_POST['testimonial_review']));

            if ($name && $review) {
                $post_id = wp_insert_post(array(
                    'post_title'   => $name,
                    'post_content' => $review,
                    'post_type'    => 'testimonial',
                    'post_status'  => 'pending',
                ));

                if ($post_id) {
                    $message = orca_text('Tak! Din anmeldelse er sendt til godkendelse.', 'Thank you! Your review has been submitted for approval.');
                } else {
                    $message = orca_text('Der opstod et problem med at sende din anmeldelse.', 'There was a problem submitting your review.');
                }
            } else {
                $message = orca_text('Indtast dit navn og din anmeldelse.', 'Please enter your name and review.');
            }
        }
    }
    ?>

    <section class="testimonial-form">
        <h2><?php echo esc_html(orca_text('Skriv en anmeldelse', 'Leave a Review')); ?></h2>

        <?php if ($message) : ?>
            <p><?php echo esc_html($message); ?></p>
        <?php endif; ?>

        <form class="testimonial-entry-form" method="post">
            <?php wp_nonce_field('orca_submit_testimonial', 'orca_testimonial_nonce'); ?>

            <p>
                <label for="testimonial_name"><?php echo esc_html(orca_text('Dit navn', 'Your Name')); ?></label><br>
                <input type="text" name="testimonial_name" id="testimonial_name" value="" required>
            </p>

            <p>
                <label for="testimonial_review"><?php echo esc_html(orca_text('Din anmeldelse', 'Your Review')); ?></label><br>
                <textarea name="testimonial_review" id="testimonial_review" rows="5" required></textarea>
            </p>

            <p>
                <button class="testimonial-entry-form__submit" type="submit" name="testimonial_submit"><?php echo esc_html(orca_text('Send anmeldelse', 'Send Review')); ?></button>
            </p>
        </form>
    </section>
</main>

<?php get_footer(); ?>
