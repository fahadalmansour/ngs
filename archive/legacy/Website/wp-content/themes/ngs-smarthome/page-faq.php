<?php
/**
 * Template Name: FAQ
 */

get_header();
?>

<main class="ngs-page-wrap" role="main">
    <?php while ( have_posts() ) : the_post(); ?>
        <section class="ngs-page-hero">
            <div class="container">
                <h1><?php the_title(); ?></h1>
                <p>إجابات واضحة لأكثر الأسئلة شيوعاً حول الطلبات، الشحن، الضمان، والدعم الفني.</p>
            </div>
        </section>

        <section class="ngs-page-content-section">
            <div class="container">
                <?php
                $raw_content = trim( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
                if ( '' === $raw_content ) :
                ?>
                    <div class="ngs-editor-note">
                        <p>أضف أسئلة وأجوبة الصفحة من المحرر. يمكن استخدام عنصر <code>Details</code> لكل سؤال/جواب للحصول على Accordion بسيط.</p>
                    </div>
                <?php else : ?>
                    <article class="ngs-entry-content ngs-faq-content">
                        <?php the_content(); ?>
                    </article>
                <?php endif; ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
