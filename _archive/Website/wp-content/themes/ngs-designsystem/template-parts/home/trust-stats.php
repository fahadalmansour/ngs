<?php
/**
 * Trust Statistics Section
 *
 * @package NGS_DesignSystem
 */
?>

<section class="ngs-trust-stats" data-ngs-section="trust-stats">
    <div class="ngs-trust-stats__container">
        <h2 class="ngs-trust-stats__title">
            <?php esc_html_e('موثوق من محبي البيوت الذكية في السعودية', 'ngs-designsystem'); ?>
        </h2>

        <div class="ngs-trust-stats__grid">
            <?php
            $stats = array(
                array(
                    'value' => get_theme_mod('ngs_stat_devices_sold', '500'),
                    'suffix' => '+',
                    'label' => __('جهاز تم بيعه', 'ngs-designsystem'),
                    'aria_label' => __('Over 500 devices sold', 'ngs-designsystem'),
                    'delay' => 0,
                ),
                array(
                    'value' => get_theme_mod('ngs_stat_rating', '4.8'),
                    'suffix' => '★',
                    'label' => __('متوسط التقييم', 'ngs-designsystem'),
                    'aria_label' => __('4.8 star average rating', 'ngs-designsystem'),
                    'delay' => 100,
                ),
                array(
                    'value' => get_theme_mod('ngs_stat_compatibility', '98'),
                    'suffix' => '%',
                    'label' => __('نسبة التوافق', 'ngs-designsystem'),
                    'aria_label' => __('98% compatibility rate', 'ngs-designsystem'),
                    'delay' => 200,
                ),
                array(
                    'value' => get_theme_mod('ngs_stat_response_time', '24'),
                    'suffix' => 'h',
                    'label' => __('وقت الاستجابة', 'ngs-designsystem'),
                    'aria_label' => __('24 hour response time', 'ngs-designsystem'),
                    'delay' => 300,
                ),
            );

            foreach ($stats as $stat) :
            ?>
            <div class="ngs-trust-stat" data-ngs-animate="fade-up" data-ngs-animate-delay="<?php echo esc_attr($stat['delay']); ?>">
                <div class="ngs-trust-stat__value"
                     data-ngs-counter="<?php echo esc_attr($stat['value']); ?>"
                     data-ngs-suffix="<?php echo esc_attr($stat['suffix']); ?>"
                     aria-label="<?php echo esc_attr($stat['aria_label']); ?>">
                    <span class="ngs-trust-stat__number">0</span><span class="ngs-trust-stat__suffix"><?php echo esc_html($stat['suffix']); ?></span>
                </div>
                <div class="ngs-trust-stat__label">
                    <?php echo esc_html($stat['label']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
