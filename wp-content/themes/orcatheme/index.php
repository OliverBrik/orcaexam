<?php get_header(); ?>

<main>

/* Displays testimonaisl on frontpage*/

    <section class="testimonial-form">
        <h2>Leave a Review</h2>
        <?php echo do_shortcode('[testimonial_form]'); ?>
    </section>

    <section class="testimonials">
        <h2>Testimonials</h2>

        <?php
        $testimonial_query = new WP_Query(array(
            'post_type' => 'testimonial',
            'post_status' => 'publish',
            'posts_per_page' => 10,
        ));

        if ($testimonial_query->have_posts()) :
            while ($testimonial_query->have_posts()) : $testimonial_query->the_post();
                ?>
                <article class="testimonial">
                    <h3><?php the_title(); ?></h3>
                    <div><?php the_content(); ?></div>
                </article>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            ?>
            <p>No testimonials yet.</p>
            <?php
        endif;
        ?>

/* End of testimonial display ^ */

    </section>
</main>

<?php get_footer(); ?>
