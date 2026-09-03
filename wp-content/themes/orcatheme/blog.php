<?php
/**
 * Template Name: Blog
 *
 * Notes for later: 
 * - The filter optioins shouldnt close when changing filters
 * - remove approval requirements for comments
 * - Add a devider between each post (Line or something)
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
        if ( ! is_user_logged_in() || ! current_user_can( 'publish_posts' ) ) {
            $error = 'Please sign in with an account that can publish posts.';
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

    if ( 'like' === $action ) {
        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! isset( $_POST['orca_blog_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['orca_blog_nonce'] ) ), 'orca_like_' . $post_id ) || 'post' !== get_post_type( $post_id ) ) {
            $error = 'That like could not be saved. Please refresh and try again.';
        } elseif ( isset( $_COOKIE['orca_liked_' . $post_id] ) ) {
            $notice = 'You have already liked this post.';
        } else {
            $likes = (int) get_post_meta( $post_id, '_orca_likes', true );
            update_post_meta( $post_id, '_orca_likes', $likes + 1 );
            setcookie( 'orca_liked_' . $post_id, '1', time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
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
                    'comment_approved'     => 0,
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
    $notice = 'Thanks! Your comment is awaiting moderation.';
}

$filter_category = absint( $_GET['blog_category'] ?? 0 );
$filter_sort     = sanitize_key( wp_unslash( $_GET['blog_sort'] ?? 'newest' ) );
$filter_sorts    = array( 'newest', 'oldest', 'most-liked', 'least-liked', 'most-commented', 'least-commented' );

if ( ! in_array( $filter_sort, $filter_sorts, true ) ) {
    $filter_sort = 'newest';
}
if ( $filter_category && ! term_exists( $filter_category, 'category' ) ) {
    $filter_category = 0;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( get_the_title() ?: 'Blog' ); ?></title>
    <style>
        body{margin:0;background:#f4f7fb;color:#18202a;font:16px/1.55 Arial,sans-serif}.orca-blog{max-width:800px;margin:0 auto;padding:48px 20px 72px}.orca-blog h1{font-size:2.3rem;margin:0 0 28px}.orca-card{background:#fff;border:1px solid #dfe6ee;border-radius:14px;padding:26px;margin:0 0 22px;box-shadow:0 5px 18px #15263a0d}.orca-card h2{margin:0 0 6px;font-size:1.45rem}.orca-meta{color:#607080;font-size:.9rem;margin:0 0 18px}.orca-categories{display:flex;flex-wrap:wrap;gap:7px;margin:0 0 18px}.orca-category-tag{display:inline-block;padding:4px 9px;border-radius:3px;color:#132016;font-size:.82rem;font-weight:700;line-height:1.2}.orca-required{color:#d7263d}.orca-category-picker{border:0;padding:0;margin:0 0 13px}.orca-category-picker legend{font-weight:700;margin-bottom:5px}.orca-category-picker label{display:inline-flex;align-items:center;gap:6px;margin:0 14px 7px 0;white-space:nowrap}.orca-category-picker input{width:auto;margin:0}.orca-filter{margin-bottom:28px}.orca-filter summary{display:inline-block;cursor:pointer;background:#1769d1;color:#fff;border-radius:7px;padding:9px 14px;font-weight:700}.orca-filter form{display:flex;flex-wrap:wrap;align-items:end;gap:12px;padding-top:14px}.orca-filter label{font-weight:700}.orca-filter select{display:block;min-width:180px;margin-top:5px;padding:9px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;font:inherit}.orca-sort-options{display:flex;gap:7px}.orca-filter .orca-sort-options button{background:#e7edf5;color:#18202a}.orca-filter .orca-sort-options button:hover{background:#d4dfed}.orca-filter .orca-sort-options .orca-active-sort{background:#1769d1;color:#fff}.orca-filter a{padding:9px 0}.orca-content{margin-bottom:20px}.orca-post-image,.orca-post-video{margin:18px 0}.orca-post-image img,.orca-post-video video{display:block;max-width:100%;height:auto;border-radius:10px}.orca-actions{display:flex;align-items:center;gap:12px;border-top:1px solid #e7ecf1;padding-top:16px}.orca-blog button{cursor:pointer;background:#1769d1;color:#fff;border:0;border-radius:7px;padding:9px 14px;font-weight:700}.orca-blog button:hover{background:#1055aa}.orca-blog input,.orca-blog textarea{box-sizing:border-box;width:100%;border:1px solid #cbd5e1;border-radius:7px;padding:10px;font:inherit;margin:5px 0 13px}.orca-blog textarea{min-height:120px;resize:vertical}.orca-comments{margin-top:22px}.orca-comments summary{cursor:pointer;font-size:1.05rem;font-weight:700;padding:12px 0}.orca-comment{border-top:1px solid #e7ecf1;padding:13px 0}.orca-comment p{margin:4px 0}.orca-form{margin-top:16px}.orca-message{padding:12px 15px;border-radius:8px;margin:0 0 20px}.orca-success{background:#e9f8ee;color:#176534}.orca-error{background:#fff0f0;color:#a12525}.orca-new-post{margin-bottom:30px}.orca-new-post summary{cursor:pointer;font-size:1.2rem;font-weight:700;margin-bottom:18px}.orca-empty{color:#607080}
    </style>
</head>
<body>
<main class="orca-blog">
    <h1><?php echo esc_html( get_the_title() ?: 'Community Blog' ); ?></h1>

    <?php if ( $notice ) : ?><p class="orca-message orca-success"><?php echo esc_html( $notice ); ?></p><?php endif; ?>
    <?php if ( $error ) : ?><p class="orca-message orca-error"><?php echo esc_html( $error ); ?></p><?php endif; ?>

    <?php if ( is_user_logged_in() && current_user_can( 'publish_posts' ) ) : ?>
        <details class="orca-card orca-new-post" <?php echo $error && 'create_post' === ( $_POST['orca_blog_action'] ?? '' ) ? 'open' : ''; ?> >
            <summary>Write a post</summary>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'orca_create_blog_post', 'orca_blog_nonce' ); ?>
                <input type="hidden" name="orca_blog_action" value="create_post">
                <label>Title <span class="orca-required" aria-hidden="true">*</span><input name="post_title" required value="<?php echo esc_attr( wp_unslash( $_POST['post_title'] ?? '' ) ); ?>"></label>
                <label>Your post <span class="orca-required" aria-hidden="true">*</span><textarea name="post_content" required><?php echo esc_textarea( wp_unslash( $_POST['post_content'] ?? '' ) ); ?></textarea></label>
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
                    <button class="<?php echo in_array( $filter_sort, array( 'most-liked', 'least-liked' ), true ) ? 'orca-active-sort' : ''; ?>" type="submit" name="blog_sort" value="<?php echo 'most-liked' === $filter_sort ? 'least-liked' : 'most-liked'; ?>">Likes<?php if ( in_array( $filter_sort, array( 'most-liked', 'least-liked' ), true ) ) : ?> <?php echo 'most-liked' === $filter_sort ? '↓' : '↑'; ?><?php endif; ?></button>
                    <button class="<?php echo in_array( $filter_sort, array( 'most-commented', 'least-commented' ), true ) ? 'orca-active-sort' : ''; ?>" type="submit" name="blog_sort" value="<?php echo 'most-commented' === $filter_sort ? 'least-commented' : 'most-commented'; ?>">Comments<?php if ( in_array( $filter_sort, array( 'most-commented', 'least-commented' ), true ) ) : ?> <?php echo 'most-commented' === $filter_sort ? '↓' : '↑'; ?><?php endif; ?></button>
                    <button class="<?php echo in_array( $filter_sort, array( 'newest', 'oldest' ), true ) ? 'orca-active-sort' : ''; ?>" type="submit" name="blog_sort" value="<?php echo 'newest' === $filter_sort ? 'oldest' : 'newest'; ?>">Date<?php if ( in_array( $filter_sort, array( 'newest', 'oldest' ), true ) ) : ?> <?php echo 'newest' === $filter_sort ? '↓' : '↑'; ?><?php endif; ?></button>
                </div>
            </div>
            <button type="submit" name="blog_sort" value="<?php echo esc_attr( $filter_sort ); ?>">Apply category</button>
            <a href="<?php echo esc_url( $page_url ); ?>">Reset</a>
        </form>
    </details>

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
            $post_id = get_the_ID();
            $likes   = (int) get_post_meta( $post_id, '_orca_likes', true );
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
                <p class="orca-meta">By <?php the_author(); ?> · <?php echo esc_html( get_the_date() ); ?></p>
                <div class="orca-content"><?php the_content(); ?></div>
                <div class="orca-actions">
                    <form method="post">
                        <?php wp_nonce_field( 'orca_like_' . $post_id, 'orca_blog_nonce' ); ?>
                        <input type="hidden" name="orca_blog_action" value="like"><input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
                        <button type="submit" <?php disabled( isset( $_COOKIE['orca_liked_' . $post_id] ) ); ?>>♥ Like</button>
                    </form>
                    <span><?php echo esc_html( $likes ); ?> <?php echo 1 === $likes ? 'like' : 'likes'; ?></span>
                </div>
                <details class="orca-comments" <?php echo $error && 'comment' === ( $_POST['orca_blog_action'] ?? '' ) && $post_id === absint( $_POST['post_id'] ?? 0 ) ? 'open' : ''; ?>>
                    <summary>Comments (<?php echo esc_html( get_comments_number( $post_id ) ); ?>)</summary>
                    <?php
                    $comments = get_comments( array( 'post_id' => $post_id, 'status' => 'approve', 'order' => 'ASC' ) );
                    foreach ( $comments as $comment ) : ?>
                        <div class="orca-comment"><strong><?php echo esc_html( $comment->comment_author ); ?></strong><p><?php echo esc_html( $comment->comment_content ); ?></p></div>
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
</main>
</body>
</html>
