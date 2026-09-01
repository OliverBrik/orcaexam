<?php get_header(); ?>

<main>

<!-- Displays testimonials on frontpage -->


    <section class="testimonial-section">
        <div class="testimonial-wrap">
            <p class="orca-contact__kicker">Reviews</p>
            <h2>What our clients say</h2>

            <div class="testimonial-grid">
                <?php
                $testimonial_query = new WP_Query(array(
                    'post_type' => 'testimonial',
                    'post_status' => 'publish',
                    'posts_per_page' => 10,
                ));

                if ($testimonial_query->have_posts()) :
                    while ($testimonial_query->have_posts()) : $testimonial_query->the_post();
                        ?>
                        <article class="testimonial-card">
                            <div class="testimonial-quote-mark">“</div>
                            <div class="testimonial-content">

                            <!-- Edits the styling review so that there is a capital letter first and no html tags are displayed.-->
                                <?php echo ucfirst(trim(strip_tags(get_the_content()))); ?>
                            </div>
                            <h3>- <?php the_title(); ?></h3>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>

 <!-- End of testimonials display on frontpage -->   
</main>

<?php get_footer(); ?>
