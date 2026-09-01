<?php
/**
 * Template Name: Community Blog
 * Template Post Type: page
 *
 * A standalone page template: it deliberately does not load this theme's
 * header or footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_url = get_permalink( get_queried_object_id() );
$notice   = '';
$error    = '';

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

            if ( '' === $title || '' === trim( wp_strip_all_tags( $content ) ) ) {
                $error = 'Please add both a title and post text.';
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
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( get_the_title() ?: 'Blog' ); ?></title>
    <style>
        body{margin:0;background:#f4f7fb;color:#18202a;font:16px/1.55 Arial,sans-serif}.orca-blog{max-width:800px;margin:0 auto;padding:48px 20px 72px}.orca-blog h1{font-size:2.3rem;margin:0 0 28px}.orca-card{background:#fff;border:1px solid #dfe6ee;border-radius:14px;padding:26px;margin:0 0 22px;box-shadow:0 5px 18px #15263a0d}.orca-card h2{margin:0 0 6px;font-size:1.45rem}.orca-meta{color:#607080;font-size:.9rem;margin:0 0 18px}.orca-content{margin-bottom:20px}.orca-post-image,.orca-post-video{margin:18px 0}.orca-post-image img,.orca-post-video video{display:block;max-width:100%;height:auto;border-radius:10px}.orca-actions{display:flex;align-items:center;gap:12px;border-top:1px solid #e7ecf1;padding-top:16px}.orca-blog button{cursor:pointer;background:#1769d1;color:#fff;border:0;border-radius:7px;padding:9px 14px;font-weight:700}.orca-blog button:hover{background:#1055aa}.orca-blog input,.orca-blog textarea{box-sizing:border-box;width:100%;border:1px solid #cbd5e1;border-radius:7px;padding:10px;font:inherit;margin:5px 0 13px}.orca-blog textarea{min-height:120px;resize:vertical}.orca-comments{margin-top:22px}.orca-comments summary{cursor:pointer;font-size:1.05rem;font-weight:700;padding:12px 0}.orca-comment{border-top:1px solid #e7ecf1;padding:13px 0}.orca-comment p{margin:4px 0}.orca-form{margin-top:16px}.orca-message{padding:12px 15px;border-radius:8px;margin:0 0 20px}.orca-success{background:#e9f8ee;color:#176534}.orca-error{background:#fff0f0;color:#a12525}.orca-new-post{margin-bottom:30px}.orca-new-post summary{cursor:pointer;font-size:1.2rem;font-weight:700;margin-bottom:18px}.orca-empty{color:#607080}
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
                <label>Title<input name="post_title" required value="<?php echo esc_attr( wp_unslash( $_POST['post_title'] ?? '' ) ); ?>"></label>
                <label>Your post<textarea name="post_content" required><?php echo esc_textarea( wp_unslash( $_POST['post_content'] ?? '' ) ); ?></textarea></label>
                <label>Add photos or videos<input type="file" name="post_media[]" accept="image/*,video/*" multiple></label>
                <button type="submit">Publish post</button>
            </form>
        </details>
    <?php endif; ?>

    <?php
    $posts = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 10, 'ignore_sticky_posts' => true ) );
    if ( $posts->have_posts() ) :
        while ( $posts->have_posts() ) : $posts->the_post();
            $post_id = get_the_ID();
            $likes   = (int) get_post_meta( $post_id, '_orca_likes', true );
            ?>
            <article class="orca-card" id="post-<?php echo esc_attr( $post_id ); ?>">
                <h2><?php the_title(); ?></h2>
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
