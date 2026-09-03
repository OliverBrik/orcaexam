<?php get_header(); ?>

<main>
    <section class="frontpage-hero">
        <div class="frontpage-hero__inner container">
            <div class="frontpage-hero__content">
                <p class="orca-contact__kicker"><?php echo esc_html(orca_text('Betroet af virksomheder i vækst', 'Trusted by growing businesses')); ?></p>
                <h1><?php echo esc_html(orca_text('Vi hjælper virksomheder sikkert fremad.', 'We help companies move forward with confidence.')); ?></h1>
                <p class="frontpage-hero__text">
                    <?php echo esc_html(orca_text('Orca hjælper virksomheder med at skabe stærkere kundeoplevelser, bedre processer og mere pålidelig support fra første dag.', 'Orca helps businesses build stronger customer experiences, better processes, and more reliable support from day one.')); ?>
                </p>

                <ul class="frontpage-hero__facts">
                    <li><?php echo esc_html(orca_text('Hurtig opstart', 'Fast onboarding')); ?></li>
                    <li><?php echo esc_html(orca_text('Tydelig kommunikation', 'Clear communication')); ?></li>
                    <li><?php echo esc_html(orca_text('Resultatorienteret service', 'Results-focused service')); ?></li>
                </ul>
            </div>

            <div class="frontpage-hero__media">
                <div class="frontpage-hero__image-wrap">
                    <img src="https://media.licdn.com/dms/image/v2/D4E22AQGF0-2mpZ_4rQ/feedshare-shrink_1280/B4EZ5MMp_CIwAQ-/0/1779394847558?e=1790208000&v=beta&t=px_g0WTCfoyk8IHBBO1NTAeLR9ff9NpvdhL6kfwyHm0" alt="<?php echo esc_attr(orca_text('Smilende team samlet på arbejdspladsen', 'Business team smiling together')); ?>" />
                </div>
            </div>
        </div>
    </section>

<!-- Displays testimonials on frontpage -->


    <section class="testimonial-section">
        <div class="testimonial-wrap">
            <p class="orca-contact__kicker"><?php echo esc_html(orca_text('Anmeldelser', 'Reviews')); ?></p>
            <h2><?php echo esc_html(orca_text('Det siger vores kunder', 'What our clients say')); ?></h2>

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
