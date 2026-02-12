<?php
/**
 * Hero Section Template
 *
 * @package NGS_DesignSystem
 */
?>

<section class="ngs-hero" data-ngs-section="hero">
    <div class="ngs-hero__container">
        <div class="ngs-hero__grid">
            <!-- Text Content -->
            <div class="ngs-hero__content" data-ngs-animate="fade-right">
                <h1 class="ngs-hero__title">
                    <?php esc_html_e('أجهزة بيت ذكي جاهزة للاستخدام في السعودية', 'ngs-designsystem'); ?>
                </h1>
                <p class="ngs-hero__subtitle">
                    <?php esc_html_e('أجهزة متوافقة مع Home Assistant ودعم فني بالعربي وخدمات تركيب', 'ngs-designsystem'); ?>
                </p>

                <!-- CTA Buttons -->
                <div class="ngs-hero__actions">
                    <a href="<?php echo esc_url(home_url('/shop/')); ?>"
                       class="ngs-btn ngs-btn--primary ngs-btn--large">
                        <?php esc_html_e('تسوق حسب النظام', 'ngs-designsystem'); ?>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4 10h12m-6-6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="<?php echo esc_url(home_url('/services/')); ?>"
                       class="ngs-btn ngs-btn--secondary ngs-btn--large">
                        <?php esc_html_e('خدمات التركيب', 'ngs-designsystem'); ?>
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="ngs-hero__badges" role="img" aria-label="<?php esc_attr_e('Trust indicators', 'ngs-designsystem'); ?>">
                    <span class="ngs-hero__badge">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M10 1l2.5 6.5L19 8.5l-5 4.5 1.5 6.5L10 16l-5.5 3.5L6 13 1 8.5l6.5-1z" fill="currentColor"/>
                        </svg>
                        Maroof
                    </span>
                    <span class="ngs-hero__badge">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="5" width="16" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M2 9h16M6 5V3a2 2 0 012-2h4a2 2 0 012 2v2" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Mada
                    </span>
                    <span class="ngs-hero__badge">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 6v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Tamara
                    </span>
                    <span class="ngs-hero__badge">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M2 10h6v8H2zM10 2h6v16h-6z" fill="currentColor"/>
                        </svg>
                        Tabby
                    </span>
                </div>
            </div>

            <!-- Hero Image -->
            <div class="ngs-hero__image" data-ngs-animate="fade-left" data-ngs-animate-delay="200">
                <?php
                $hero_image_id = get_theme_mod('ngs_hero_image');
                if ($hero_image_id) {
                    echo wp_get_attachment_image(
                        $hero_image_id,
                        'full',
                        false,
                        array(
                            'class' => 'ngs-hero__img',
                            'alt' => __('Smart home devices', 'ngs-designsystem'),
                            'loading' => 'eager',
                        )
                    );
                } else {
                    // Placeholder
                    ?>
                    <div class="ngs-hero__img-placeholder" role="img" aria-label="<?php esc_attr_e('Smart home devices placeholder', 'ngs-designsystem'); ?>">
                        <svg width="600" height="600" viewBox="0 0 600 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="600" height="600" fill="#E5E7EB"/>
                            <path d="M300 200l100 100-100 100-100-100z" fill="#9CA3AF"/>
                            <circle cx="300" cy="300" r="50" fill="#6B7280"/>
                        </svg>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</section>
