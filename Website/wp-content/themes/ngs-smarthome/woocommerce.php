<?php
/**
 * The template for displaying WooCommerce pages
 * 
 * This file wraps the WooCommerce content in the theme's layout structure.
 */

get_header();
?>

<div class="container" style="padding: 4rem 1rem;">
    <?php
    if ( have_posts() ) :
        woocommerce_content();
    endif;
    ?>
</div>

<?php
get_footer();
