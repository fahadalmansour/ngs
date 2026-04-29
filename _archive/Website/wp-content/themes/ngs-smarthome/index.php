<?php
/**
 * The main template file
 */

get_header();
?>

<div class="container" style="padding: 4rem 1rem;">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            ?>
            <div class="entry-content">
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </div>
            <?php
        endwhile;
    else :
        ?>
        <p><?php esc_html_e( 'عذراً، لم يتم العثور على محتوى.', 'ngs-smarthome' ); ?></p>
        <?php
    endif;
    ?>
</div>

<?php
get_footer();
