<?php
/**
 * Template Name: Blog
 */
require get_theme_file_path('/inc/blog-page.php');
get_header();
?>


<main id="primary" class="orca-blog">
    <section class="orca-blog__hero">
        <div class="orca-blog__hero-inner">
            <h1><?php echo esc_html( get_the_title() ?: orca_text('Blog', 'Blog') ); ?></h1>
            <p><?php echo esc_html( orca_text('Idéer, opdateringer og samtaler fra Orca-teamet.', 'Ideas, updates, and conversations from the Orca team.') ); ?></p>
        </div>
    </section>

    <section class="orca-blog__content">
        <div class="orca-blog__shell">

    <?php if ( $notice ) : ?><p class="orca-message orca-success"><?php echo esc_html( $notice ); ?></p><?php endif; ?>
    <?php if ( $error ) : ?><p class="orca-message orca-error"><?php echo esc_html( $error ); ?></p><?php endif; ?>

    <div class="orca-blog__tools">
    <?php if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) : ?>
        <details class="orca-card orca-new-post" <?php echo $error && 'create_post' === ( $_POST['orca_blog_action'] ?? '' ) ? 'open' : ''; ?> >
            <summary><?php echo esc_html( orca_text('Skriv et indlæg', 'Write a post') ); ?></summary>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'orca_create_blog_post', 'orca_blog_nonce' ); ?>
                <input type="hidden" name="orca_blog_action" value="create_post">
                <label><span class="orca-field-label"><?php echo esc_html( orca_text('Titel', 'Title') ); ?> <span class="orca-required" aria-hidden="true">*</span></span><input name="post_title" required value="<?php echo esc_attr( wp_unslash( $_POST['post_title'] ?? '' ) ); ?>"></label>
                <label><span class="orca-field-label"><?php echo esc_html( orca_text('Dit indlæg', 'Your post') ); ?> <span class="orca-required" aria-hidden="true">*</span></span><textarea name="post_content" required><?php echo esc_textarea( wp_unslash( $_POST['post_content'] ?? '' ) ); ?></textarea></label>
                <fieldset class="orca-category-picker">
                    <legend><?php echo esc_html( orca_text('Kategorier', 'Categories') ); ?> <span class="orca-required" aria-hidden="true">*</span></legend>
                    <?php
                    $selected_categories = isset( $_POST['post_categories'] ) && is_array( $_POST['post_categories'] ) ? array_map( 'absint', wp_unslash( $_POST['post_categories'] ) ) : array();
                    foreach ( get_categories( array( 'hide_empty' => false, 'exclude' => array( (int) get_option( 'default_category' ) ) ) ) as $category ) : ?>
                        <label><input type="checkbox" name="post_categories[]" value="<?php echo esc_attr( $category->term_id ); ?>" <?php checked( in_array( $category->term_id, $selected_categories, true ) ); ?>><?php echo esc_html( $category->name ); ?></label>
                    <?php endforeach; ?>
                </fieldset>
                <label><?php echo esc_html( orca_text('Tilføj billeder eller videoer', 'Add photos or videos') ); ?><input type="file" name="post_media[]" accept="image/*,video/*" multiple></label>
                <button type="submit"><?php echo esc_html( orca_text('Udgiv indlæg', 'Publish post') ); ?></button>
            </form>
        </details>
    <?php endif; ?>

    <details class="orca-filter">
        <summary><?php echo esc_html( orca_text('Filter', 'Filter') ); ?></summary>
        <form method="get" action="<?php echo esc_url( $page_url ); ?>">
            <label><?php echo esc_html( orca_text('Kategori', 'Category') ); ?>
                <select name="blog_category">
                    <option value="0"><?php echo esc_html( orca_text('Alle kategorier', 'All categories') ); ?></option>
                    <?php foreach ( get_categories( array( 'hide_empty' => false, 'exclude' => array( (int) get_option( 'default_category' ) ) ) ) as $filter_term ) : ?>
                        <option value="<?php echo esc_attr( $filter_term->term_id ); ?>" <?php selected( $filter_category, $filter_term->term_id ); ?>><?php echo esc_html( $filter_term->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div>
                <strong><?php echo esc_html( orca_text('Sortér efter', 'Sort by') ); ?></strong>
                <div class="orca-sort-options">
                    <button class="<?php echo in_array( $applied_sort, array( 'most-liked', 'least-liked' ), true ) ? 'orca-active-sort' : ''; ?>" type="button" data-sort-desc="most-liked" data-sort-asc="least-liked"><?php echo esc_html( orca_text('Likes', 'Likes') ); ?> <span class="orca-sort-arrow" aria-hidden="true"><?php echo 'most-liked' === $applied_sort ? '↓' : ( 'least-liked' === $applied_sort ? '↑' : '' ); ?></span></button>
                    <button class="<?php echo in_array( $applied_sort, array( 'most-commented', 'least-commented' ), true ) ? 'orca-active-sort' : ''; ?>" type="button" data-sort-desc="most-commented" data-sort-asc="least-commented"><?php echo esc_html( orca_text('Kommentarer', 'Comments') ); ?> <span class="orca-sort-arrow" aria-hidden="true"><?php echo 'most-commented' === $applied_sort ? '↓' : ( 'least-commented' === $applied_sort ? '↑' : '' ); ?></span></button>
                    <button class="<?php echo in_array( $applied_sort, array( 'newest', 'oldest' ), true ) ? 'orca-active-sort' : ''; ?>" type="button" data-sort-desc="newest" data-sort-asc="oldest"><?php echo esc_html( orca_text('Dato', 'Date') ); ?> <span class="orca-sort-arrow" aria-hidden="true"><?php echo 'newest' === $applied_sort ? '↓' : ( 'oldest' === $applied_sort ? '↑' : '' ); ?></span></button>
                </div>
            </div>
            <input type="hidden" name="blog_sort" value="<?php echo esc_attr( $applied_sort ); ?>">
            <button type="submit"><?php echo esc_html( orca_text('Anvend filter', 'Apply filter') ); ?></button>
            <a href="<?php echo esc_url( $page_url ); ?>"><?php echo esc_html( orca_text('Nulstil', 'Reset') ); ?></a>
        </form>
    </details>
    </div>

    <?php
    $post_query_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 10,
        'paged'               => max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
        'ignore_sticky_posts' => true,
    );
    if ( $filter_category ) {
        $post_query_args['category__in'] = array( $filter_category );
    }
    if ( in_array( $filter_sort, array( 'most-liked', 'least-liked' ), true ) ) {
        $post_query_args['meta_query'] = array(
            'relation' => 'OR',
            'likes'    => array(
                'key'     => '_orca_likes',
                'compare' => 'EXISTS',
                'type'    => 'NUMERIC',
            ),
            'no_likes' => array(
                'key'     => '_orca_likes',
                'compare' => 'NOT EXISTS',
            ),
        );
        $post_query_args['orderby'] = 'likes';
        $post_query_args['order']   = 'most-liked' === $filter_sort ? 'DESC' : 'ASC';
    } elseif ( in_array( $filter_sort, array( 'most-commented', 'least-commented' ), true ) ) {
        $post_query_args['orderby'] = 'comment_count';
        $post_query_args['order']   = 'most-commented' === $filter_sort ? 'DESC' : 'ASC';
    } else {
        $post_query_args['orderby'] = 'date';
        $post_query_args['order']   = 'oldest' === $filter_sort ? 'ASC' : 'DESC';
    }
    $posts = new WP_Query( $post_query_args );
    if ( $posts->have_posts() ) :
        while ( $posts->have_posts() ) : $posts->the_post();
            $post_id            = get_the_ID();
            $likes              = (int) get_post_meta( $post_id, '_orca_likes', true );
            $post_timestamp     = get_post_time( 'U', true );
            $post_age           = max( 0, current_time( 'timestamp', true ) - (int) $post_timestamp );
            if ( $post_age < MINUTE_IN_SECONDS ) {
                $post_time = $post_age . ' ' . ( 1 === $post_age ? orca_text('sekund', 'second') : orca_text('sekunder', 'seconds') ) . ' ' . orca_text('siden', 'ago');
            } elseif ( $post_age < HOUR_IN_SECONDS ) {
                $post_minutes = (int) floor( $post_age / MINUTE_IN_SECONDS );
                $post_time    = $post_minutes . ' ' . ( 1 === $post_minutes ? orca_text('minut', 'minute') : orca_text('minutter', 'minutes') ) . ' ' . orca_text('siden', 'ago');
            } elseif ( $post_age < DAY_IN_SECONDS ) {
                $post_hours = (int) floor( $post_age / HOUR_IN_SECONDS );
                $post_time  = $post_hours . ' ' . ( 1 === $post_hours ? orca_text('time', 'hour') : orca_text('timer', 'hours') ) . ' ' . orca_text('siden', 'ago');
            } else {
                $post_time = wp_date( 'F j - Y', (int) $post_timestamp );
            }
            ?>
            <article class="orca-card" id="post-<?php echo esc_attr( $post_id ); ?>">
                <h2><?php echo esc_html(get_the_title()); ?></h2>
                <?php $post_categories = get_the_category( $post_id ); if ( $post_categories ) : ?>
                    <div class="orca-categories">
                        <?php foreach ( $post_categories as $post_category ) :
                            $tag_colour = $orca_category_colours[ $post_category->slug ] ?? sprintf( 'hsl(%d, 70%%, 78%%)', ( $post_category->term_id * 47 ) % 360 ); ?>
                            <span class="orca-category-tag" style="background-color:<?php echo esc_attr( $tag_colour ); ?>"><?php echo esc_html( $post_category->name ); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <p class="orca-meta"><?php echo esc_html( orca_text('Af', 'By') ); ?> <?php echo esc_html(get_the_author()); ?> · <?php echo esc_html( $post_time ); ?></p>
                <div class="orca-content"><?php echo orca_safe_post_content(); ?></div>
                <div class="orca-actions">
                    <form method="post">
                        <?php wp_nonce_field( 'orca_like_' . $post_id, 'orca_blog_nonce' ); ?>
                        <input type="hidden" name="orca_blog_action" value="like"><input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
                        <?php $already_liked = isset( $_COOKIE['orca_liked_' . $post_id] ); ?>
                        <button class="<?php echo $already_liked ? 'orca-liked' : ''; ?>" type="submit" data-orca-like><?php echo esc_html( $already_liked ? orca_text('♥ Liket', '♥ Liked') : orca_text('♥ Like', '♥ Like') ); ?></button>
                    </form>
                    <span class="orca-like-count"><?php echo esc_html( $likes ); ?> <?php echo esc_html( 1 === $likes ? orca_text('like', 'like') : orca_text('likes', 'likes') ); ?></span>
                </div>
                <details class="orca-comments" <?php echo $error && 'comment' === ( $_POST['orca_blog_action'] ?? '' ) && $post_id === absint( $_POST['post_id'] ?? 0 ) ? 'open' : ''; ?>>
                    <summary><?php echo esc_html( orca_text('Kommentarer', 'Comments') ); ?> (<?php echo esc_html( get_comments_number( $post_id ) ); ?>)</summary>
                    <?php
                    $comments = get_comments( array( 'post_id' => $post_id, 'status' => 'approve', 'order' => 'ASC' ) );
                    foreach ( $comments as $comment ) :
                        $comment_timestamp = get_comment_date( 'U', $comment, false );
                        $comment_age       = max( 0, current_time( 'timestamp' ) - (int) $comment_timestamp );
                        if ( $comment_age < MINUTE_IN_SECONDS ) {
                            $comment_time = $comment_age . ' ' . ( 1 === $comment_age ? orca_text('sekund', 'second') : orca_text('sekunder', 'seconds') ) . ' ' . orca_text('siden', 'ago');
                        } elseif ( $comment_age < HOUR_IN_SECONDS ) {
                            $comment_minutes = (int) floor( $comment_age / MINUTE_IN_SECONDS );
                            $comment_time    = $comment_minutes . ' ' . ( 1 === $comment_minutes ? orca_text('minut', 'minute') : orca_text('minutter', 'minutes') ) . ' ' . orca_text('siden', 'ago');
                        } elseif ( $comment_age < DAY_IN_SECONDS ) {
                            $comment_hours = (int) floor( $comment_age / HOUR_IN_SECONDS );
                            $comment_time  = $comment_hours . ' ' . ( 1 === $comment_hours ? orca_text('time', 'hour') : orca_text('timer', 'hours') ) . ' ' . orca_text('siden', 'ago');
                        } else {
                            $comment_time = wp_date( 'F j - Y', (int) $comment_timestamp );
                        }
                        ?>
                        <div class="orca-comment"><strong><?php echo esc_html( $comment->comment_author ); ?></strong> <span class="orca-comment-time"><?php echo esc_html( $comment_time ); ?></span><p><?php echo esc_html( $comment->comment_content ); ?></p></div>
                    <?php endforeach; ?>
                    <?php if ( comments_open( $post_id ) ) : ?>
                        <form class="orca-form" method="post">
                            <?php wp_nonce_field( 'orca_comment_' . $post_id, 'orca_blog_nonce' ); ?>
                            <input type="hidden" name="orca_blog_action" value="comment"><input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
                            <h3 class="orca-comments__heading"><?php echo esc_html( orca_text('Deltag i samtalen', 'Join the conversation') ); ?></h3>
                            <?php if ( ! is_user_logged_in() ) : ?>
                                <label><?php echo esc_html( orca_text('Navn', 'Name') ); ?> <span>*</span><input name="comment_author" autocomplete="name" required></label>
                                <label><?php echo esc_html( orca_text('E-mail', 'Email') ); ?> <span>*</span><input type="email" name="comment_email" autocomplete="email" required></label>
                            <?php endif; ?>
                            <label class="orca-comments__message"><?php echo esc_html( orca_text('Din kommentar', 'Your comment') ); ?> <span>*</span><textarea name="comment" rows="5" placeholder="<?php echo esc_attr( orca_text('Del dine tanker om indlægget…', 'Share your thoughts on this post…') ); ?>" required></textarea></label>
                            <button type="submit"><?php echo esc_html( orca_text('Send kommentar', 'Post comment') ); ?> <span aria-hidden="true">→</span></button>
                        </form>
                    <?php endif; ?>
                </details>
            </article>
        <?php endwhile; wp_reset_postdata();
    else : ?>
        <p class="orca-empty"><?php echo esc_html( orca_text('Der er endnu ingen blogindlæg.', 'There are no blog posts yet.') ); ?></p>
    <?php endif; ?>
    <?php if ( $posts->max_num_pages > 1 ) : ?>
        <nav class="orca-pagination" aria-label="<?php echo esc_attr( orca_text('Blognavigation', 'Blog pagination') ); ?>">
            <?php
            echo wp_kses_post( paginate_links( array(
                'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                'format'    => '?paged=%#%',
                'current'   => max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
                'total'     => $posts->max_num_pages,
                'type'      => 'list',
                'prev_text' => esc_html( orca_text('Forrige', 'Previous') ),
                'next_text' => esc_html( orca_text('Næste', 'Next') ),
                'add_args'  => array_filter( array(
                    'blog_category' => $filter_category,
                    'blog_sort'     => $filter_sort,
                ) ),
            ) ) );
            ?>
        </nav>
    <?php endif; ?>
        </div>
    </section>
</main>


<?php get_footer(); ?>
