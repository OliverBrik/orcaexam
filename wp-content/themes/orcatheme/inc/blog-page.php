<?php
/** Blog submissions and filter state, evaluated before output. */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_url = get_permalink( get_queried_object_id() );
$notice   = '';
$error    = '';
$orca_category_colours = array(
    'updates'     => '#60d96a',
    'events'      => '#f4a261',
    'announcements' => '#c084fc',
);

/* Only create starter categories during an authorized form submission. */
if ( 'POST' === ($_SERVER['REQUEST_METHOD'] ?? '')
    && 'create_post' === ($_POST['orca_blog_action'] ?? '')
    && current_user_can('manage_options') && current_user_can('manage_categories')
    && orca_verify_form_nonce($_POST['orca_blog_nonce'] ?? '', 'orca_create_blog_post') ) {
    foreach ( array( 'Updates', 'Events', 'Announcements' ) as $orca_category_name ) {
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
            $error = 'Kun administratorer kan udgive indlæg.';
        } elseif ( ! isset( $_POST['orca_blog_nonce'] ) || ! orca_verify_form_nonce( $_POST['orca_blog_nonce'], 'orca_create_blog_post' ) ) {
            $error = 'Din session er udløbet. Prøv igen.';
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
                $error = 'Tilføj både en titel og tekst til indlægget.';
            } elseif ( empty( $category_ids ) ) {
                $error = 'Vælg mindst én kategori.';
            } else {
                $post_id = wp_insert_post( array(
                    'post_title'   => $title,
                    'post_content' => $content,
                    'post_status'  => 'publish',
                    'post_type'    => 'post',
                    'post_author'  => get_current_user_id(),
                ), true );

                if ( is_wp_error( $post_id ) ) {
                    $error = 'Indlægget kunne ikke udgives. Prøv igen.';
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
                                $media_html .= '<figure class="orca-post-video"><video controls preload="metadata"><source src="' . esc_url( wp_get_attachment_url( $attachment_id ) ) . '" type="' . esc_attr( get_post_mime_type( $attachment_id ) ) . '">' . esc_html( 'Din browser understøtter ikke denne video.' ) . '</video></figure>';
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
        if ( ! isset( $_POST['orca_blog_nonce'] ) || ! orca_verify_form_nonce( $_POST['orca_blog_nonce'], 'orca_like_' . $post_id ) || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status($post_id) ) {
            if ( $is_ajax ) {
                wp_send_json_error( array( 'message' => 'Dit like kunne ikke gemmes. Prøv igen.' ), 400 );
            }
            $error = 'Dit like kunne ikke gemmes. Opdater siden, og prøv igen.';
        } elseif ( 'like' === $action && isset( $_COOKIE['orca_liked_' . $post_id] ) ) {
            if ( $is_ajax ) {
                wp_send_json_success( array( 'likes' => (int) get_post_meta( $post_id, '_orca_likes', true ), 'liked' => true ) );
            }
            $notice = 'Du har allerede liket dette indlæg.';
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
        if ( ! isset( $_POST['orca_blog_nonce'] ) || ! orca_verify_form_nonce( $_POST['orca_blog_nonce'], 'orca_comment_' . $post_id ) || 'post' !== get_post_type($post_id) || 'publish' !== get_post_status($post_id) || ! comments_open( $post_id ) ) {
            $error = 'Kommentarer er ikke tilgængelige for dette indlæg.';
        } else {
            $comment = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );
            $name    = sanitize_text_field( wp_unslash( $_POST['comment_author'] ?? '' ) );
            $email   = sanitize_email( wp_unslash( $_POST['comment_email'] ?? '' ) );
            $user    = wp_get_current_user();

            if ( '' === $comment || ( ! is_user_logged_in() && ( '' === $name || ! is_email( $email ) ) ) ) {
                $error = 'Indtast en kommentar, navn og en gyldig e-mailadresse.';
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
                $error = 'Din kommentar kunne ikke gemmes. Prøv igen.';
            }
        }
    }
}

if ( 'published' === ( $_GET['blog_notice'] ?? '' ) ) {
    $notice = 'Dit indlæg er udgivet.';
} elseif ( 'liked' === ( $_GET['blog_notice'] ?? '' ) ) {
    $notice = 'Tak for dit like!';
} elseif ( 'commented' === ( $_GET['blog_notice'] ?? '' ) ) {
    $notice = 'Tak! Din kommentar er udgivet.';
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
