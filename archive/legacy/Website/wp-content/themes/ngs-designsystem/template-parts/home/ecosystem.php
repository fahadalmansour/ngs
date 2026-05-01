<?php
/**
 * Ecosystem Cards Section
 *
 * @package NGS_DesignSystem
 */
?>

<section class="ngs-ecosystem ngs-bg-alt" data-ngs-section="ecosystem">
    <div class="ngs-ecosystem__container">
        <div class="ngs-ecosystem__header">
            <h2 class="ngs-ecosystem__title">
                <?php esc_html_e('تسوق حسب النظام', 'ngs-designsystem'); ?>
            </h2>
            <p class="ngs-ecosystem__subtitle">
                <?php esc_html_e('ابحث عن الأجهزة المتوافقة مع منصة بيتك الذكي', 'ngs-designsystem'); ?>
            </p>
        </div>

        <div class="ngs-ecosystem__grid">
            <?php
            $ecosystems = array(
                array(
                    'name' => __('Home Assistant', 'ngs-designsystem'),
                    'slug' => 'home-assistant',
                    'description' => __('منصة البيت الذكي الأقوى والأكثر مرونة مع دعم لجميع البروتوكولات', 'ngs-designsystem'),
                    'protocols' => array('Zigbee', 'Z-Wave', 'WiFi', 'Thread'),
                    'image_id' => get_theme_mod('ngs_ecosystem_ha_image'),
                ),
                array(
                    'name' => __('Apple HomeKit', 'ngs-designsystem'),
                    'slug' => 'homekit',
                    'description' => __('تحكم في بيتك باستخدام Siri ونظام آبل البيئي المتكامل', 'ngs-designsystem'),
                    'protocols' => array('Thread', 'WiFi', 'Bluetooth'),
                    'image_id' => get_theme_mod('ngs_ecosystem_homekit_image'),
                ),
                array(
                    'name' => __('Amazon Alexa', 'ngs-designsystem'),
                    'slug' => 'alexa',
                    'description' => __('أجهزة متوافقة مع Alexa للتحكم الصوتي السهل', 'ngs-designsystem'),
                    'protocols' => array('WiFi', 'Zigbee', 'Bluetooth'),
                    'image_id' => get_theme_mod('ngs_ecosystem_alexa_image'),
                ),
                array(
                    'name' => __('Google Home', 'ngs-designsystem'),
                    'slug' => 'google-home',
                    'description' => __('اربط أجهزتك مع Google Assistant والنظام البيئي لجوجل', 'ngs-designsystem'),
                    'protocols' => array('WiFi', 'Thread', 'Matter'),
                    'image_id' => get_theme_mod('ngs_ecosystem_google_image'),
                ),
            );

            foreach ($ecosystems as $index => $ecosystem) :
            ?>
            <div class="ngs-ecosystem-card" data-ngs-animate="fade-up" data-ngs-animate-delay="<?php echo esc_attr($index * 100); ?>">
                <div class="ngs-ecosystem-card__image">
                    <?php
                    if ($ecosystem['image_id']) {
                        echo wp_get_attachment_image(
                            $ecosystem['image_id'],
                            'medium',
                            false,
                            array(
                                'class' => 'ngs-ecosystem-card__img',
                                'alt' => sprintf(
                                    /* translators: %s: Ecosystem name */
                                    __('%s devices', 'ngs-designsystem'),
                                    $ecosystem['name']
                                ),
                            )
                        );
                    } else {
                        // Placeholder
                        ?>
                        <div class="ngs-ecosystem-card__img-placeholder" role="img" aria-label="<?php echo esc_attr(sprintf(__('%s devices placeholder', 'ngs-designsystem'), $ecosystem['name'])); ?>">
                            <svg width="400" height="300" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="400" height="300" fill="#E5E7EB"/>
                                <path d="M200 100l50 50-50 50-50-50z" fill="#9CA3AF"/>
                            </svg>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <div class="ngs-ecosystem-card__content">
                    <h3 class="ngs-ecosystem-card__title">
                        <?php echo esc_html($ecosystem['name']); ?>
                    </h3>

                    <div class="ngs-ecosystem-card__protocols" role="list" aria-label="<?php esc_attr_e('Supported protocols', 'ngs-designsystem'); ?>">
                        <?php foreach ($ecosystem['protocols'] as $protocol) : ?>
                            <span class="ngs-badge ngs-badge--outline" role="listitem">
                                <?php echo esc_html($protocol); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <p class="ngs-ecosystem-card__description">
                        <?php echo esc_html($ecosystem['description']); ?>
                    </p>

                    <a href="<?php echo esc_url(home_url('/shop/' . $ecosystem['slug'] . '/')); ?>"
                       class="ngs-btn ngs-btn--outline ngs-btn--full">
                        <?php
                        /* translators: %s: Ecosystem name */
                        printf(esc_html__('Browse %s Devices', 'ngs-designsystem'), esc_html($ecosystem['name']));
                        ?>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4 10h12m-6-6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
