<?php wp_head(); ?>
<main class="orca-blog orca-single" id="main-content">
<?php while (have_posts()) : the_post(); ?>
    <article <?php post_class('orca-single-post'); ?>>
        <header class="orca-single-post__header">
            <div class="orca-post-card__meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(sprintf(__('Published %s', 'orcatheme'), get_the_date())); ?></time><?php if (get_the_modified_time('U') !== get_the_time('U')) : ?><span><?php echo esc_html(sprintf(__('Updated %s', 'orcatheme'), get_the_modified_date())); ?></span><?php endif; ?></div>
            <h1><?php the_title(); ?></h1>
            <div class="orca-tags"><?php the_tags('', ' '); ?></div>
        </header>
        <?php if (has_post_thumbnail()) : ?><div class="orca-single-post__featured"><?php the_post_thumbnail('large'); ?></div><?php endif; ?>
        <div class="orca-single-post__content"><?php the_content(); ?></div>
        <footer class="orca-single-post__footer"><?php orca_like_button(); ?></footer>
    </article>
    <?php if (comments_open() || get_comments_number()) comments_template(); ?>
<?php endwhile; ?>
</main>
<?php wp_footer(); ?>
