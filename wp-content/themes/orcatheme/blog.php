<?php
/**
 * Notes for later: 
 * - The filter optioins shouldnt close when changing filters
 * - remove approval requirements for comments
 * - Add a devider between each post (Line or something)
 * - Only we need to be able to make posts, not other people
 * - Fix the filter options so that the page only reloads when the "apply filter" is clicked and gives a preview of the filter before pressing apply
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_url = get_permalink( get_queried_object_id() );
$notice   = '';
$error    = '';
$orca_category_colours = array(
    'updates'     => '#60d96a',
    'events'      => '#f4a261',
    'study-tips'  => '#63b3ed',
    'announcements' => '#c084fc',
);

/* Create a few useful categories once when an administrator visits the blog. */
if ( current_user_can( 'manage_categories' ) ) {
    foreach ( array( 'Updates', 'Events', 'Study Tips', 'Announcements' ) as $orca_category_name ) {
        if ( ! term_exists( $orca_category_name, 'category' ) ) {
            wp_insert_term( $orca_category_name, 'category' );
        }
    }
}

/* Handle all form submissions before any page output. */
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['orca_blog_action'] ) ) {
    $action = sanitize_key( wp_unslash( $_POST['orca_blog_action'] ) );

    if ( 'create_post' === $action ) {
        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            $error = 'Only site administrators can publish posts.';
        } elseif ( ! isset( $_POST['orca_blog_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['orca_blog_nonce'] ) ), 'orca_create_blog_post' ) ) {
            $error = 'Your session expired. Please try again.';
        } else {
            $title   = sanitize_text_field( wp_unslash( $_POST['post_title'] ?? '' ) );
            $content = wp_kses_post( wp_unslash( $_POST['post_content'] ?? '' ) );
            $category_ids = isset( $_POST['post_categories'] ) && is_array( $_POST['post_categories'] )
                ? array_filter( array_map( 'absint', wp_unslash( $_POST['post_categories'] ) ) )
                : array();
            if ( $category_ids ) {
                $category_ids = get_terms( array(
                    'taxonomy'   => 'category',
                    'include'    => $category_ids,
                    'exclude'    => array( (int) get_option( 'default_category' ) ),
                    'fields'     => 'ids',
                    'hide_empty' => false,
                ) );
            }

            if ( '' === $title || '' === trim( wp_strip_all_tags( $content ) ) ) {
                $error = 'Please add both a title and post text.';
            } elseif ( empty( $category_ids ) ) {
                $error = 'Please choose at least one category.';
            } else {
                $post_id = wp_insert_post( array(
                    'post_title'   => $title,
                    'post_content' => $content,
                    'post_status'  => 'publish',
                    'post_type'    => 'post',
                    'post_author'  => get_current_user_id(),
                ), true );

                if ( is_wp_error( $post_id ) ) {
                    $error = 'The post could not be published. Please try again.';
                } else {
                    if ( $category_ids ) {
                        wp_set_post_categories( $post_id, $category_ids, false );
                    }

                    /* Save each selected image or video in the Media Library and add it to the post. */
                    if ( ! empty( $_FILES['post_media']['name'] ) && is_array( $_FILES['post_media']['name'] ) ) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        require_once ABSPATH . 'wp-admin/includes/media.php';

                        $original_files = $_FILES['post_media'];
                        $media_html     = '';

                        foreach ( $original_files['name'] as $index => $filename ) {
                            if ( UPLOAD_ERR_NO_FILE === (int) $original_files['error'][ $index ] ) {
                                continue;
                            }

                            $_FILES['orca_post_media'] = array(
                                'name'     => $filename,
                                'type'     => $original_files['type'][ $index ],
                                'tmp_name' => $original_files['tmp_name'][ $index ],
                                'error'    => $original_files['error'][ $index ],
                                'size'     => $original_files['size'][ $index ],
                            );
                            $attachment_id = media_handle_upload( 'orca_post_media', $post_id );

                            if ( ! is_wp_error( $attachment_id ) && wp_attachment_is_image( $attachment_id ) ) {
                                $media_html .= '<figure class="orca-post-image">' . wp_get_attachment_image( $attachment_id, 'large', false, array( 'loading' => 'lazy' ) ) . '</figure>';
                            } elseif ( ! is_wp_error( $attachment_id ) && 0 === strpos( (string) get_post_mime_type( $attachment_id ), 'video/' ) ) {
                                $media_html .= '<figure class="orca-post-video"><video controls preload="metadata"><source src="' . esc_url( wp_get_attachment_url( $attachment_id ) ) . '" type="' . esc_attr( get_post_mime_type( $attachment_id ) ) . '">Your browser does not support this video.</video></figure>';
                            }
                        }

                        unset( $_FILES['orca_post_media'] );
                        if ( $media_html ) {
                            wp_update_post( array( 'ID' => $post_id, 'post_content' => $content . $media_html ) );
                        }
                    }
                    wp_safe_redirect( add_query_arg( 'blog_notice', 'published', $page_url ) );
                    exit;
                }
            }
        }
    }

    if ( in_array( $action, array( 'like', 'unlike' ), true ) ) {
        $post_id  = absint( $_POST['post_id'] ?? 0 );
        $is_ajax  = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' === strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) );
        if ( ! isset( $_POST['orca_blog_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['orca_blog_nonce'] ) ), 'orca_like_' . $post_id ) || 'post' !== get_post_type( $post_id ) ) {
            if ( $is_ajax ) {
                wp_send_json_error( array( 'message' => 'That like could not be saved. Please try again.' ), 400 );
            }
            $error = 'That like could not be saved. Please refresh and try again.';
        } elseif ( 'like' === $action && isset( $_COOKIE['orca_liked_' . $post_id] ) ) {
            if ( $is_ajax ) {
                wp_send_json_success( array( 'likes' => (int) get_post_meta( $post_id, '_orca_likes', true ), 'liked' => true ) );
            }
            $notice = 'You have already liked this post.';
        } elseif ( 'unlike' === $action ) {
            $likes = max( 0, (int) get_post_meta( $post_id, '_orca_likes', true ) - 1 );
            update_post_meta( $post_id, '_orca_likes', $likes );
            setcookie( 'orca_liked_' . $post_id, '', time() - YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
            if ( $is_ajax ) {
                wp_send_json_success( array( 'likes' => $likes, 'liked' => false ) );
            }
            wp_safe_redirect( add_query_arg( 'blog_notice', 'unliked', $page_url ) . '#post-' . $post_id );
            exit;
        } else {
            $likes = (int) get_post_meta( $post_id, '_orca_likes', true );
            update_post_meta( $post_id, '_orca_likes', $likes + 1 );
            setcookie( 'orca_liked_' . $post_id, '1', time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
            if ( $is_ajax ) {
                wp_send_json_success( array( 'likes' => $likes + 1, 'liked' => true ) );
            }
            wp_safe_redirect( add_query_arg( 'blog_notice', 'liked', $page_url ) . '#post-' . $post_id );
            exit;
        }
    }

    if ( 'comment' === $action ) {
        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! isset( $_POST['orca_blog_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['orca_blog_nonce'] ) ), 'orca_comment_' . $post_id ) || ! comments_open( $post_id ) ) {
            $error = 'Comments are not available for this post.';
        } else {
            $comment = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );
            $name    = sanitize_text_field( wp_unslash( $_POST['comment_author'] ?? '' ) );
            $email   = sanitize_email( wp_unslash( $_POST['comment_email'] ?? '' ) );
            $user    = wp_get_current_user();

            if ( '' === $comment || ( ! is_user_logged_in() && ( '' === $name || ! is_email( $email ) ) ) ) {
                $error = 'Please enter a comment, name, and valid email address.';
            } else {
                $comment_id = wp_insert_comment( array(
                    'comment_post_ID'      => $post_id,
                    'comment_content'      => $comment,
                    'comment_author'       => is_user_logged_in() ? $user->display_name : $name,
                    'comment_author_email' => is_user_logged_in() ? $user->user_email : $email,
                    'user_id'              => is_user_logged_in() ? $user->ID : 0,
                    'comment_approved'     => 1,
                ) );
                if ( $comment_id ) {
                    wp_safe_redirect( add_query_arg( 'blog_notice', 'commented', $page_url ) . '#post-' . $post_id );
                    exit;
                }
                $error = 'Your comment could not be saved. Please try again.';
            }
        }
    }
}

if ( 'published' === ( $_GET['blog_notice'] ?? '' ) ) {
    $notice = 'Your post has been published.';
} elseif ( 'liked' === ( $_GET['blog_notice'] ?? '' ) ) {
    $notice = 'Thanks for liking this post!';
} elseif ( 'commented' === ( $_GET['blog_notice'] ?? '' ) ) {
    $notice = 'Thanks! Your comment has been posted.';
}

$filter_category = absint( $_GET['blog_category'] ?? 0 );
$applied_sort    = sanitize_key( wp_unslash( $_GET['blog_sort'] ?? '' ) );
$filter_sorts    = array( 'newest', 'oldest', 'most-liked', 'least-liked', 'most-commented', 'least-commented' );

if ( ! in_array( $applied_sort, $filter_sorts, true ) ) {
    $applied_sort = '';
}
$filter_sort = $applied_sort ?: 'newest';
if ( $filter_category && ! term_exists( $filter_category, 'category' ) ) {
    $filter_category = 0;
}
?>
<?php get_header(); ?>

<main id="primary" class="orca-blog">
    <section class="orca-blog__hero">
        <div class="orca-blog__hero-inner">
            <h1><?php echo esc_html( get_the_title() ?: 'Community Blog' ); ?></h1>
            <p>Ideas, updates, and conversations from the Orca team.</p>
        </div>
    </section>

    <section class="orca-blog__content">
        <div class="orca-blog__shell">

    <?php if ( $notice ) : ?><p class="orca-message orca-success"><?php echo esc_html( $notice ); ?></p><?php endif; ?>
    <?php if ( $error ) : ?><p class="orca-message orca-error"><?php echo esc_html( $error ); ?></p><?php endif; ?>

    <div class="orca-blog__tools">
    <?php if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) : ?>
        <details class="orca-card orca-new-post" <?php echo $error && 'create_post' === ( $_POST['orca_blog_action'] ?? '' ) ? 'open' : ''; ?> >
            <summary>Write a post</summary>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'orca_create_blog_post', 'orca_blog_nonce' ); ?>
                <input type="hidden" name="orca_blog_action" value="create_post">
                <label><span class="orca-field-label">Title <span class="orca-required" aria-hidden="true">*</span></span><input name="post_title" required value="<?php echo esc_attr( wp_unslash( $_POST['post_title'] ?? '' ) ); ?>"></label>
                <label><span class="orca-field-label">Your post <span class="orca-required" aria-hidden="true">*</span></span><textarea name="post_content" required><?php echo esc_textarea( wp_unslash( $_POST['post_content'] ?? '' ) ); ?></textarea></label>
                <fieldset class="orca-category-picker">
                    <legend>Categories <span class="orca-required" aria-hidden="true">*</span></legend>
                    <?php
                    $selected_categories = isset( $_POST['post_categories'] ) && is_array( $_POST['post_categories'] ) ? array_map( 'absint', wp_unslash( $_POST['post_categories'] ) ) : array();
                    foreach ( get_categories( array( 'hide_empty' => false, 'exclude' => array( (int) get_option( 'default_category' ) ) ) ) as $category ) : ?>
                        <label><input type="checkbox" name="post_categories[]" value="<?php echo esc_attr( $category->term_id ); ?>" <?php checked( in_array( $category->term_id, $selected_categories, true ) ); ?>><?php echo esc_html( $category->name ); ?></label>
                    <?php endforeach; ?>
                </fieldset>
                <label>Add photos or videos<input type="file" name="post_media[]" accept="image/*,video/*" multiple></label>
                <button type="submit">Publish post</button>
            </form>
        </details>
    <?php endif; ?>

    <details class="orca-filter">
        <summary>Filter</summary>
        <form method="get" action="<?php echo esc_url( $page_url ); ?>">
            <label>Category
                <select name="blog_category">
                    <option value="0">All categories</option>
                    <?php foreach ( get_categories( array( 'hide_empty' => false, 'exclude' => array( (int) get_option( 'default_category' ) ) ) ) as $filter_term ) : ?>
                        <option value="<?php echo esc_attr( $filter_term->term_id ); ?>" <?php selected( $filter_category, $filter_term->term_id ); ?>><?php echo esc_html( $filter_term->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div>
                <strong>Sort by</strong>
                <div class="orca-sort-options">
                    <button class="<?php echo in_array( $applied_sort, array( 'most-liked', 'least-liked' ), true ) ? 'orca-active-sort' : ''; ?>" type="button" data-sort-desc="most-liked" data-sort-asc="least-liked">Likes <span class="orca-sort-arrow" aria-hidden="true"><?php echo 'most-liked' === $applied_sort ? '↓' : ( 'least-liked' === $applied_sort ? '↑' : '' ); ?></span></button>
                    <button class="<?php echo in_array( $applied_sort, array( 'most-commented', 'least-commented' ), true ) ? 'orca-active-sort' : ''; ?>" type="button" data-sort-desc="most-commented" data-sort-asc="least-commented">Comments <span class="orca-sort-arrow" aria-hidden="true"><?php echo 'most-commented' === $applied_sort ? '↓' : ( 'least-commented' === $applied_sort ? '↑' : '' ); ?></span></button>
                    <button class="<?php echo in_array( $applied_sort, array( 'newest', 'oldest' ), true ) ? 'orca-active-sort' : ''; ?>" type="button" data-sort-desc="newest" data-sort-asc="oldest">Date <span class="orca-sort-arrow" aria-hidden="true"><?php echo 'newest' === $applied_sort ? '↓' : ( 'oldest' === $applied_sort ? '↑' : '' ); ?></span></button>
                </div>
            </div>
            <input type="hidden" name="blog_sort" value="<?php echo esc_attr( $applied_sort ); ?>">
            <button type="submit">Apply filter</button>
            <a href="<?php echo esc_url( $page_url ); ?>">Reset</a>
        </form>
    </details>
    </div>

    <?php
    $post_query_args = array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'ignore_sticky_posts' => true );
    if ( $filter_category ) {
        $post_query_args['category__in'] = array( $filter_category );
    }
    $posts = new WP_Query( $post_query_args );

    usort( $posts->posts, function ( $first_post, $second_post ) use ( $filter_sort ) {
        if ( 'most-liked' === $filter_sort ) {
            return (int) get_post_meta( $second_post->ID, '_orca_likes', true ) <=> (int) get_post_meta( $first_post->ID, '_orca_likes', true );
        }
        if ( 'least-liked' === $filter_sort ) {
            return (int) get_post_meta( $first_post->ID, '_orca_likes', true ) <=> (int) get_post_meta( $second_post->ID, '_orca_likes', true );
        }
        if ( 'most-commented' === $filter_sort ) {
            return (int) $second_post->comment_count <=> (int) $first_post->comment_count;
        }
        if ( 'least-commented' === $filter_sort ) {
            return (int) $first_post->comment_count <=> (int) $second_post->comment_count;
        }
        $date_order = strtotime( $second_post->post_date ) <=> strtotime( $first_post->post_date );
        return 'oldest' === $filter_sort ? -$date_order : $date_order;
    } );
    $posts->post_count = count( $posts->posts );
    $posts->rewind_posts();
    if ( $posts->have_posts() ) :
        while ( $posts->have_posts() ) : $posts->the_post();
            $post_id            = get_the_ID();
            $likes              = (int) get_post_meta( $post_id, '_orca_likes', true );
            $post_timestamp     = get_post_time( 'U', true );
            $post_age           = max( 0, current_time( 'timestamp', true ) - (int) $post_timestamp );
            if ( $post_age < MINUTE_IN_SECONDS ) {
                $post_time = $post_age . ' ' . ( 1 === $post_age ? 'second' : 'seconds' ) . ' ago';
            } elseif ( $post_age < HOUR_IN_SECONDS ) {
                $post_minutes = (int) floor( $post_age / MINUTE_IN_SECONDS );
                $post_time    = $post_minutes . ' ' . ( 1 === $post_minutes ? 'minute' : 'minutes' ) . ' ago';
            } elseif ( $post_age < DAY_IN_SECONDS ) {
                $post_hours = (int) floor( $post_age / HOUR_IN_SECONDS );
                $post_time  = $post_hours . ' ' . ( 1 === $post_hours ? 'hour' : 'hours' ) . ' ago';
            } else {
                $post_time = wp_date( 'F j - Y', (int) $post_timestamp );
            }
            ?>
            <article class="orca-card" id="post-<?php echo esc_attr( $post_id ); ?>">
                <h2><?php the_title(); ?></h2>
                <?php $post_categories = get_the_category( $post_id ); if ( $post_categories ) : ?>
                    <div class="orca-categories">
                        <?php foreach ( $post_categories as $post_category ) :
                            $tag_colour = $orca_category_colours[ $post_category->slug ] ?? sprintf( 'hsl(%d, 70%%, 78%%)', ( $post_category->term_id * 47 ) % 360 ); ?>
                            <span class="orca-category-tag" style="background-color:<?php echo esc_attr( $tag_colour ); ?>"><?php echo esc_html( $post_category->name ); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <p class="orca-meta">By <?php the_author(); ?> · <?php echo esc_html( $post_time ); ?></p>
                <div class="orca-content"><?php the_content(); ?></div>
                <div class="orca-actions">
                    <form method="post">
                        <?php wp_nonce_field( 'orca_like_' . $post_id, 'orca_blog_nonce' ); ?>
                        <input type="hidden" name="orca_blog_action" value="like"><input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
                        <?php $already_liked = isset( $_COOKIE['orca_liked_' . $post_id] ); ?>
                        <button class="<?php echo $already_liked ? 'orca-liked' : ''; ?>" type="submit" data-orca-like><?php echo $already_liked ? '♥ Liked' : '♥ Like'; ?></button>
                    </form>
                    <span class="orca-like-count"><?php echo esc_html( $likes ); ?> <?php echo 1 === $likes ? 'like' : 'likes'; ?></span>
                </div>
                <details class="orca-comments" <?php echo $error && 'comment' === ( $_POST['orca_blog_action'] ?? '' ) && $post_id === absint( $_POST['post_id'] ?? 0 ) ? 'open' : ''; ?>>
                    <summary>Comments (<?php echo esc_html( get_comments_number( $post_id ) ); ?>)</summary>
                    <?php
                    $comments = get_comments( array( 'post_id' => $post_id, 'status' => 'approve', 'order' => 'ASC' ) );
                    foreach ( $comments as $comment ) :
                        $comment_timestamp = get_comment_date( 'U', $comment, false );
                        $comment_age       = max( 0, current_time( 'timestamp' ) - (int) $comment_timestamp );
                        if ( $comment_age < MINUTE_IN_SECONDS ) {
                            $comment_time = $comment_age . ' ' . ( 1 === $comment_age ? 'second' : 'seconds' ) . ' ago';
                        } elseif ( $comment_age < HOUR_IN_SECONDS ) {
                            $comment_minutes = (int) floor( $comment_age / MINUTE_IN_SECONDS );
                            $comment_time    = $comment_minutes . ' ' . ( 1 === $comment_minutes ? 'minute' : 'minutes' ) . ' ago';
                        } elseif ( $comment_age < DAY_IN_SECONDS ) {
                            $comment_hours = (int) floor( $comment_age / HOUR_IN_SECONDS );
                            $comment_time  = $comment_hours . ' ' . ( 1 === $comment_hours ? 'hour' : 'hours' ) . ' ago';
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
                            <?php if ( ! is_user_logged_in() ) : ?><label>Name<input name="comment_author" required></label><label>Email<input type="email" name="comment_email" required></label><?php endif; ?>
                            <label>Join the conversation<textarea name="comment" required></textarea></label><button type="submit">Post comment</button>
                        </form>
                    <?php endif; ?>
                </details>
            </article>
        <?php endwhile; wp_reset_postdata();
    else : ?>
        <p class="orca-empty">There are no blog posts yet.</p>
    <?php endif; ?>
        </div>
    </section>
</main>

<script>
(function () {
    document.querySelectorAll('.orca-actions form').forEach(function (form) {
        const button = form.querySelector('[data-orca-like]');
        const count = form.closest('.orca-actions').querySelector('.orca-like-count');
        if (!button || !count) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            button.disabled = true;
            const formData = new FormData(form);
            formData.set('orca_blog_action', button.classList.contains('orca-liked') ? 'unlike' : 'like');

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (response) {
                    if (!response.success || !response.data || typeof response.data.liked !== 'boolean') {
                        throw new Error('Like could not be saved.');
                    }

                    const likes = Number(response.data.likes);
                    button.textContent = response.data.liked ? '♥ Liked' : '♥ Like';
                    button.classList.toggle('orca-liked', response.data.liked);
                    button.disabled = false;
                    count.textContent = likes + ' ' + (1 === likes ? 'like' : 'likes');
                })
                .catch(function () {
                    button.disabled = false;
                });
        });
    });

})();

(function () {
    const filterPanel = document.querySelector('.orca-filter');
    const filterForm = document.querySelector('.orca-filter form');
    if (!filterPanel || !filterForm) {
        return;
    }

    const sortButtons = filterForm.querySelectorAll('[data-sort-desc]');
    const sortInput = filterForm.querySelector('input[name="blog_sort"]');

    sortButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!sortInput) {
                return;
            }

            const arrow = button.querySelector('.orca-sort-arrow');
            const currentState = arrow ? arrow.textContent.trim() : '';
            const selectedSort = '↓' === currentState
                ? button.getAttribute('data-sort-asc')
                : '↑' === currentState
                    ? ''
                    : button.getAttribute('data-sort-desc');

            sortButtons.forEach(function (item) {
                const itemArrow = item.querySelector('.orca-sort-arrow');
                const isSelected = item === button && selectedSort;
                item.classList.toggle('orca-active-sort', Boolean(isSelected));
                if (itemArrow) {
                    itemArrow.textContent = isSelected ? (selectedSort === item.getAttribute('data-sort-desc') ? '↓' : '↑') : '';
                }
            });
            sortInput.value = selectedSort || '';
        });
    });

    document.addEventListener('click', function (event) {
        if (filterPanel.open && !filterPanel.contains(event.target)) {
            filterPanel.removeAttribute('open');
        }
    });
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
