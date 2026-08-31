<?php get_header(); ?>

<main>

    <section class="testimonial-form">
        <h2>Leave a Review</h2>
        <?php echo do_shortcode('[testimonial_form]'); ?>
    </section>

    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class(); ?>>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>    
</main>

<?php
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
</section>

<?php get_footer(); ?>
