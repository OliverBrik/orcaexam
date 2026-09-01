<?php get_header(); ?>

<main>

<!-- Displays testimonials on frontpage -->

    <section class="testimonials">
        <h2>Testimonials</h2>
        <p>Here are some of our customer reviews:</p>
        <?php
        $testimonial_query = new WP_Query(array(
            'post_type' => 'testimonial',
            'post_status' => 'publish',
            'posts_per_page' => 10,
        ));

/* Loops through each testimonial and print the review title and content.*/

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

 /* if there is no apporoved reviews, it will display a message "No approved reviews yet." */
            ?>
            <p>No testimonials yet.</p>
            <?php
        endif;
        ?>

<!--End of testimonial display ^ -->

    </section>
</main>

<?php get_footer(); ?>
