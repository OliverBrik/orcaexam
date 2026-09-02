<?php get_header(); ?>

<main>
    <section class="frontpage-hero">
        <div class="frontpage-hero__inner container">
            <div class="frontpage-hero__content">
                <p class="orca-contact__kicker">Trusted by growing businesses</p>
                <h1>We help companies move forward with confidence.</h1>
                <p class="frontpage-hero__text">
                    Orca helps businesses build stronger customer experiences, better processes,
                    and more reliable support from day one.
                </p>

                <ul class="frontpage-hero__facts">
                    <li>Fast onboarding</li>
                    <li>Clear communication</li>
                    <li>Results-focused service</li>
                </ul>
            </div>

            <div class="frontpage-hero__media">
                <div class="frontpage-hero__image-wrap">
                    <img src="https://picsum.photos/seed/orca-frontpage/900/760" alt="Business team smiling together" />
                </div>
            </div>
        </div>
    </section>

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
