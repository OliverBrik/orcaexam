<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_queried_object_id();

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['orca_single_comment'] ) ) {
    $redirect_url = get_permalink( $post_id );
    $comment      = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );
    $user         = wp_get_current_user();
    $name         = is_user_logged_in() ? $user->display_name : sanitize_text_field( wp_unslash( $_POST['comment_author'] ?? '' ) );
    $email        = is_user_logged_in() ? $user->user_email : sanitize_email( wp_unslash( $_POST['comment_email'] ?? '' ) );

    if ( ! isset( $_POST['orca_single_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['orca_single_nonce'] ) ), 'orca_single_comment_' . $post_id ) ) {
        $redirect_url = add_query_arg( 'discussion_status', 'error', $redirect_url );
    } elseif ( '' === $comment || '' === $name || ! is_email( $email ) || ! comments_open( $post_id ) ) {
        $redirect_url = add_query_arg( 'discussion_status', 'invalid', $redirect_url );
    } else {
        $comment_id = wp_insert_comment( array(
            'comment_post_ID'      => $post_id,
            'comment_content'      => $comment,
            'comment_author'       => $name,
            'comment_author_email' => $email,
            'user_id'              => is_user_logged_in() ? $user->ID : 0,
            'comment_approved'     => 0,
        ) );
        $redirect_url = add_query_arg( 'discussion_status', $comment_id ? 'sent' : 'error', $redirect_url );
    }

    wp_safe_redirect( $redirect_url . '#discussion' );
    exit;
}

get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        $post_id = get_the_ID();
        ?>
        <main class="orca-single-post">
            <article class="orca-single-post__content">
                <p class="orca-contact__kicker"><?php echo esc_html( orca_text('Fra Orca-fællesskabet', 'From the Orca community') ); ?></p>
                <h1><?php the_title(); ?></h1>
                <p class="orca-meta"><?php echo esc_html( orca_text('Af', 'By') ); ?> <?php the_author(); ?> · <?php echo esc_html( get_the_date() ); ?></p>
                <?php the_content(); ?>
            </article>

            <section class="orca-discussion" id="discussion">
                <p class="orca-contact__kicker"><?php echo esc_html( orca_text('Samtale', 'Discussion') ); ?></p>
                <h2><?php echo esc_html( orca_text('Hvad tænker du?', 'What do you think?') ); ?></h2>

                <?php if ( 'sent' === ( $_GET['discussion_status'] ?? '' ) ) : ?>
                    <p class="orca-message orca-success"><?php echo esc_html( orca_text('Tak! Din kommentar afventer godkendelse.', 'Thanks! Your comment is awaiting moderation.') ); ?></p>
                <?php elseif ( in_array( $_GET['discussion_status'] ?? '', array( 'error', 'invalid' ), true ) ) : ?>
                    <p class="orca-message orca-error"><?php echo esc_html( orca_text('Kommentaren kunne ikke sendes. Kontrollér felterne, og prøv igen.', 'Your comment could not be sent. Check the fields and try again.') ); ?></p>
                <?php endif; ?>

                <?php foreach ( get_comments( array( 'post_id' => $post_id, 'status' => 'approve', 'order' => 'ASC' ) ) as $comment ) : ?>
                    <div class="orca-comment">
                        <strong><?php echo esc_html( $comment->comment_author ); ?></strong>
                        <p><?php echo esc_html( $comment->comment_content ); ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if ( comments_open( $post_id ) ) : ?>
                    <form class="orca-discussion__form" method="post">
                        <?php wp_nonce_field( 'orca_single_comment_' . $post_id, 'orca_single_nonce' ); ?>
                        <?php if ( ! is_user_logged_in() ) : ?>
                            <label><?php echo esc_html( orca_text('Navn', 'Name') ); ?><input name="comment_author" required></label>
                            <label><?php echo esc_html( orca_text('E-mail', 'Email') ); ?><input type="email" name="comment_email" required></label>
                        <?php endif; ?>
                        <label><?php echo esc_html( orca_text('Din kommentar', 'Your comment') ); ?><textarea name="comment" required></textarea></label>
                        <button type="submit" name="orca_single_comment"><?php echo esc_html( orca_text('Send kommentar', 'Post comment') ); ?></button>
                    </form>
                <?php endif; ?>
            </section>
        </main>
        <?php
    endwhile;
else :
    ?>
    <main class="orca-single-post"><p class="orca-empty"><?php echo esc_html( orca_text('Indlægget blev ikke fundet.', 'The post could not be found.') ); ?></p></main>
    <?php
endif;

get_footer();