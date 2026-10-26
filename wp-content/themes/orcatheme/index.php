<?php get_header(); ?>

<main>
    <section class="frontpage-hero">
        <div class="frontpage-hero__inner container">
            <div class="frontpage-hero__content">
                <p class="orca-kicker"><?php echo esc_html('Betroet af virksomheder i vækst'); ?></p>
                <h1><?php echo esc_html('Vi hjælper virksomheder sikkert fremad.'); ?></h1>
                <p class="frontpage-hero__text">
                    <?php echo esc_html('Orca hjælper virksomheder med at skabe stærkere kundeoplevelser, bedre processer og mere pålidelig support fra første dag.'); ?>
                </p>

                <ul class="frontpage-hero__facts">
                    <li><?php echo esc_html('Hurtig opstart'); ?></li>
                    <li><?php echo esc_html('Tydelig kommunikation'); ?></li>
                    <li><?php echo esc_html('Resultatorienteret service'); ?></li>
                </ul>
            </div>

            <div class="frontpage-hero__media">
                <?php
                $gallery_directory = trailingslashit( get_stylesheet_directory() ) . 'gallery/';
                $gallery_url       = trailingslashit( get_stylesheet_directory_uri() ) . 'gallery/';
                $gallery_files     = glob( $gallery_directory . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE );
                $gallery_files     = is_array( $gallery_files ) ? $gallery_files : array();
                sort( $gallery_files, SORT_NATURAL | SORT_FLAG_CASE );
                $hero_dimensions = $gallery_files ? getimagesize( $gallery_files[0] ) : false;
                ?>
                <div class="frontpage-hero__gallery" aria-label="<?php echo esc_attr( 'Billedgalleri' ); ?>">
                    <?php if ( $gallery_files ) : ?>
                        <div class="frontpage-hero__image-wrap">
                            <div class="frontpage-hero__image-frame">
                                <img src="<?php echo esc_url( $gallery_url . rawurlencode( basename( $gallery_files[0] ) ) ); ?>" alt="<?php echo esc_attr( 'Billede fra Orca-galleriet' ); ?>"<?php echo is_array( $hero_dimensions ) ? ' width="' . esc_attr( $hero_dimensions[0] ) . '" height="' . esc_attr( $hero_dimensions[1] ) . '"' : ''; ?> loading="eager" fetchpriority="high" decoding="async" />
                            </div>
                        </div>
                        <?php if ( count( $gallery_files ) > 1 ) : ?>
                            <div class="frontpage-hero__gallery-strip">
                                <?php foreach ( array_slice( $gallery_files, 1 ) as $gallery_index => $gallery_file ) : ?>
                                    <div data-gallery-src="<?php echo esc_url( $gallery_url . rawurlencode( basename( $gallery_file ) ) ); ?>">
                                        <img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="<?php echo esc_attr( sprintf( '%s %d', 'Billede fra galleriet', $gallery_index + 2 ) ); ?>" data-gallery-lazy="true" />
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="frontpage-hero__image-wrap">
                            <img src="<?php echo esc_url(get_theme_file_uri('/contact-media/orca-staff.jpg')); ?>" alt="<?php echo esc_attr('Smilende team samlet på arbejdspladsen'); ?>" width="2048" height="1536" loading="eager" fetchpriority="high" decoding="async" />
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<!-- Displays testimonials on frontpage -->


    <section class="testimonial-section">
        <div class="testimonial-wrap">
            <p class="orca-kicker"><?php echo esc_html('Anmeldelser'); ?></p>
            <h2><?php echo esc_html('Det siger vores kunder'); ?></h2>

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
                                <?php echo esc_html(ucfirst(trim(wp_strip_all_tags(get_the_content())))); ?>
                            </div>
                            <h3>- <?php echo esc_html(get_the_title()); ?></h3>
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
