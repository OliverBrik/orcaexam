<?php
/*
Template Name: Submit Testimonial
*/
get_header();
?>

<main class="testimonial-page">
    <?php

/* Stores the status message that will be shown after submission.*/

    $message = '';

 /* If the form is submitted, this will validate the request and save the review as a pending testimonial.*/

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['testimonial_submit'])) {
        if (!isset($_POST['orca_testimonial_nonce']) || !wp_verify_nonce($_POST['orca_testimonial_nonce'], 'orca_submit_testimonial')) {
            $message = 'Security check failed.';
        } else {

/* Clean the input before saving it.*/
       
            $name = sanitize_text_field(wp_unslash($_POST['testimonial_name']));
            $review = sanitize_textarea_field(wp_unslash($_POST['testimonial_review']));

/* Only create the testimonial if both fields are filled. */

            if ($name && $review) {
                $post_id = wp_insert_post(array(
                    'post_title'   => $name,
                    'post_content' => $review,
                    'post_type'    => 'testimonial',
                    'post_status'  => 'pending',
                ));

/* Shows a success or error message based on whether the post was created.*/


                if ($post_id) {
                    $message = 'Thank you! Your review has been submitted for approval.';
                } else {
                    $message = 'There was a problem submitting your review.';
                }
            } else {
                $message = 'Please enter your name and review.';
            }
        }
    }
    ?>

    <section class="testimonial-form">
        <h2>Leave a Review</h2>

        <?php if ($message) : ?>
            <p><?php echo esc_html($message); ?></p>
        <?php endif; ?>

        <form method="post" style="max-width:600px; margin-top:30px;">
            <?php wp_nonce_field('orca_submit_testimonial', 'orca_testimonial_nonce'); ?>

            <p>
                <label for="testimonial_name">Your Name</label><br>
                <input type="text" name="testimonial_name" id="testimonial_name" value="" required style="width:100%; padding:10px;">
            </p>

            <p>
                <label for="testimonial_review">Your Review</label><br>
                <textarea name="testimonial_review" id="testimonial_review" rows="5" required style="width:100%; padding:10px;"></textarea>
            </p>

            <p>
                <button type="submit" name="testimonial_submit">Send Review</button>
            </p>
        </form>
    </section>
</main>

<?php get_footer(); ?>