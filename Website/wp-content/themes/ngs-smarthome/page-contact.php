<?php
/**
 * Template Name: Contact Us
 */

get_header();

$has_whatsapp = function_exists( 'ngs_has_whatsapp_number' ) ? ngs_has_whatsapp_number() : false;
$whatsapp_display = function_exists( 'ngs_get_whatsapp_display' ) ? ngs_get_whatsapp_display() : '+966 5X XXX XXXX';
$whatsapp_url = function_exists( 'ngs_get_whatsapp_link' )
    ? ngs_get_whatsapp_link( 'مرحباً، أحتاج مساعدة بخصوص متجر NGS' )
    : '#';
?>

<main class="ngs-page-wrap" role="main">
    <?php while ( have_posts() ) : the_post(); ?>
        <section class="ngs-page-hero">
            <div class="container">
                <h1><?php the_title(); ?></h1>
                <p>تواصل معنا بالطريقة الأنسب لك، وسنساعدك في أقرب وقت.</p>
            </div>
        </section>

        <section class="ngs-page-content-section">
            <div class="container">
                <div class="ngs-contact-quick-grid">
                    <div class="ngs-contact-quick-card">
                        <h3>واتساب</h3>
                        <p><?php echo esc_html( $whatsapp_display ); ?></p>
                        <a class="btn btn-primary<?php echo $has_whatsapp ? '' : ' is-disabled'; ?>" href="<?php echo esc_url( $whatsapp_url ); ?>"<?php echo $has_whatsapp ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            تواصل واتساب
                        </a>
                    </div>
                    <div class="ngs-contact-quick-card">
                        <h3>البريد الإلكتروني</h3>
                        <p>support@ngs-smarthome.sa</p>
                        <p class="ngs-contact-muted">Placeholder: حدّث البريد النهائي من إعدادات الموقع.</p>
                    </div>
                    <div class="ngs-contact-quick-card">
                        <h3>العنوان</h3>
                        <p>الرياض، المملكة العربية السعودية</p>
                        <p class="ngs-contact-muted">متجر إلكتروني فقط - لا يوجد معرض</p>
                    </div>
                </div>

                <?php
                $raw_content = trim( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
                if ( '' === $raw_content ) :
                ?>
                    <div class="ngs-editor-note">
                        أضف نموذج التواصل ومحتوى صفحة "تواصل معنا" من المحرر.
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
