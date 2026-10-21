<?php
/** Shared document closing tags and WordPress/plugin footer assets. */
defined('ABSPATH') || exit;
if (orca_is_commerce_page()) {
    get_template_part('template-parts/shop/footer');
}
wp_footer();
?>
</body>
</html>
