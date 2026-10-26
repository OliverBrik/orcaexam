<?php
defined('ABSPATH') || exit;
get_header('shop');
?>
<main class="orca-shop orca-shop--transaction"><div class="container orca-shop__content">
    <nav class="orca-shop__back"><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">← <?php echo esc_html('Tilbage til webshoppen'); ?></a></nav>
    <?php while (have_posts()) : the_post(); ?>
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>
    <?php endwhile; ?>
</div></main>
<?php get_footer('shop'); ?>
