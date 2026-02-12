<?php
/**
 * Template Name: Front Page
 */

get_header();
?>

<main class="ngs-home-page" role="main">
    <section class="ngs-page-hero ngs-page-hero-home">
        <div class="container">
            <h1><?php the_title(); ?></h1>
            <p>صفحة رئيسية مُدارة من المحرر. أضف أقسامك من أنماط NGS واستخدم الشورت كود للعناصر الديناميكية.</p>
        </div>
    </section>

    <section class="ngs-page-content-section">
        <div class="container ngs-home-content">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php
                    $raw_content = trim( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
                    if ( '' === $raw_content ) :
                    ?>
                        <div class="ngs-editor-note">
                            <h2>المحتوى غير مضاف بعد</h2>
                            <p>أضف محتوى الصفحة من محرر ووردبريس باستخدام الأنماط الجاهزة في قسم <strong>NGS Drafts</strong>.</p>
                            <p>يمكنك إدراج عناصر ديناميكية عبر الشورت كود:</p>
                            <ul>
                                <li><code>[ngs_featured_categories]</code></li>
                                <li><code>[ngs_best_sellers limit="3" columns="3"]</code></li>
                                <li><code>[ngs_trust_badges]</code></li>
                                <li><code>[ngs_whatsapp_cta]</code></li>
                            </ul>
                        </div>
                    <?php else : ?>
                        <article class="ngs-entry-content">
                            <?php the_content(); ?>
                        </article>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
