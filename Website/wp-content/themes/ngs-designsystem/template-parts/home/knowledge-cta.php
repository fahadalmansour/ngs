<?php
/**
 * Knowledge Base CTA Section
 *
 * @package NGS_DesignSystem
 */
?>

<section class="ngs-knowledge-cta" data-ngs-section="knowledge-cta">
    <div class="ngs-knowledge-cta__container">
        <div class="ngs-knowledge-cta__grid">
            <!-- Icon -->
            <div class="ngs-knowledge-cta__icon" data-ngs-animate="fade-right">
                <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect x="20" y="15" width="80" height="90" rx="4" stroke="currentColor" stroke-width="3"/>
                    <path d="M30 30h60M30 45h60M30 60h40M30 75h50" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="85" cy="85" r="20" fill="var(--ngs-primary)" stroke="white" stroke-width="3"/>
                    <path d="M77 85l5 5 11-11" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="ngs-knowledge-cta__content" data-ngs-animate="fade-left" data-ngs-animate-delay="100">
                <h2 class="ngs-knowledge-cta__title">
                    <?php esc_html_e('تحتاج مساعدة في اختيار الجهاز المناسب؟', 'ngs-designsystem'); ?>
                </h2>
                <p class="ngs-knowledge-cta__description">
                    <?php esc_html_e('قاعدة المعرفة لدينا تغطي مقارنات البروتوكولات وأدلة الإعداد ومصفوفات التوافق.', 'ngs-designsystem'); ?>
                </p>

                <div class="ngs-knowledge-cta__actions">
                    <a href="<?php echo esc_url(home_url('/knowledge-base/')); ?>"
                       class="ngs-btn ngs-btn--primary">
                        <?php esc_html_e('تصفح قاعدة المعرفة', 'ngs-designsystem'); ?>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4 10h12m-6-6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>

                    <?php
                    $whatsapp = get_theme_mod('ngs_whatsapp_number');
                    if ($whatsapp) :
                        $whatsapp_url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp);
                        $whatsapp_message = urlencode(__('مرحباً، أحتاج مساعدة في اختيار الجهاز المناسب', 'ngs-designsystem'));
                        $whatsapp_url .= '?text=' . $whatsapp_message;
                    ?>
                        <a href="<?php echo esc_url($whatsapp_url); ?>"
                           class="ngs-knowledge-cta__link"
                           target="_blank"
                           rel="noopener noreferrer">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M10.002 0h-.004C4.486 0 0 4.486 0 10c0 2.19.706 4.214 1.902 5.856L.657 19.5l3.771-1.212A9.958 9.958 0 0010.002 20C15.514 20 20 15.514 20 10S15.514 0 10.002 0zm5.848 14.164c-.246.692-1.448 1.268-1.998 1.348-.538.078-1.048.246-3.534-.758-3.005-1.214-4.942-4.246-5.09-4.442-.146-.196-1.196-1.596-1.196-3.044 0-1.448.758-2.16 1.026-2.456.268-.296.586-.37.782-.37.196 0 .392.002.564.01.18.01.422-.068.66.504.246.588.842 2.056.916 2.204.074.148.124.32.024.516-.1.196-.148.32-.296.492-.148.172-.31.384-.442.516-.148.148-.302.308-.13.604.172.296.766 1.266 1.644 2.05 1.13.998 2.082 1.31 2.378 1.458.296.148.468.124.64-.074.172-.198.738-.862 1.006-1.16.268-.296.536-.246.808-.148.272.1 1.726.814 2.022.962.296.148.492.222.564.344.074.124.074.714-.172 1.406z"/>
                            </svg>
                            <?php esc_html_e('أو تواصل معنا عبر واتساب', 'ngs-designsystem'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
