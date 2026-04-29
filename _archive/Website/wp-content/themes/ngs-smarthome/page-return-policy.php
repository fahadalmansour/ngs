<?php
/**
 * Template Name: Return Policy
 */

get_header();
?>

<main class="ngs-page-wrap" role="main">
    <?php while ( have_posts() ) : the_post(); ?>
        <section class="ngs-page-hero">
            <div class="container">
                <h1><?php the_title(); ?></h1>
                <p>آخر تحديث: <?php echo esc_html( get_the_modified_date( 'F Y' ) ); ?></p>
            </div>
        </section>

        <section class="ngs-page-content-section">
            <div class="container">
                <?php
                $raw_content = trim( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
                if ( '' === $raw_content ) :
                ?>
                    <div class="ngs-editor-note">
                        أضف سياسة الاسترجاع الكاملة من المحرر مع البنود الرسمية وفترات المعالجة.
                    </div>
                <?php else : ?>
                    <article class="ngs-entry-content ngs-legal-content">
                        <?php the_content(); ?>
                    </article>
                <?php endif; ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
