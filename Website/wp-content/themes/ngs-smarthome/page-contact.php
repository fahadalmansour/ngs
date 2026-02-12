<?php
/**
 * Template Name: Contact Us
 */

get_header();

$whatsapp_raw = (string) get_option( 'neogen_whatsapp_number', '' );
$whatsapp_digits = preg_replace( '/\D+/', '', $whatsapp_raw );
$has_whatsapp = strlen( $whatsapp_digits ) >= 9;
$whatsapp_display = $has_whatsapp ? '+' . $whatsapp_digits : '+966 5X XXX XXXX';
$whatsapp_url = $has_whatsapp
    ? 'https://wa.me/' . $whatsapp_digits . '?text=' . rawurlencode( 'مرحباً، أحتاج مساعدة بخصوص متجر NGS' )
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
