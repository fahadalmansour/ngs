<?php
/**
 * Template Name: About Us
 */

get_header();
?>

<main class="ngs-page-wrap" role="main">
    <?php while ( have_posts() ) : the_post(); ?>
        <section class="ngs-page-hero">
            <div class="container">
                <h1><?php the_title(); ?></h1>
                <?php if ( has_excerpt() ) : ?>
                    <p><?php echo esc_html( get_the_excerpt() ); ?></p>
                <?php else : ?>
                    <p>تعرف على قصة NGS ورؤيتنا في بناء بيوت ذكية أسهل للمجتمع العربي.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="ngs-page-content-section">
            <div class="container">
                <?php
                $raw_content = trim( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
                if ( '' === $raw_content ) :
                ?>
                    <div class="ngs-editor-note">
                        أضف محتوى صفحة "من نحن" من المحرر. يمكنك استخدام نمط <strong>NGS Drafts</strong> المناسب.
                    </div>
                <?php else : ?>
                    <article class="ngs-entry-content">
                        <?php the_content(); ?>
                    </article>
                <?php endif; ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
