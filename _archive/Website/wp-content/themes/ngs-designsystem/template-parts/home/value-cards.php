<?php
/**
 * Value Proposition Cards Section
 *
 * @package NGS_DesignSystem
 */
?>

<section class="ngs-value-cards" data-ngs-section="value-cards">
    <div class="ngs-value-cards__container">
        <div class="ngs-value-cards__grid">
            <?php
            $value_cards = array(
                array(
                    'title' => __('متوافق مع Home Assistant', 'ngs-designsystem'),
                    'description' => __('جميع الأجهزة معدة مسبقًا للعمل مع Home Assistant دون تعقيدات', 'ngs-designsystem'),
                    'icon' => '<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="10" y="10" width="60" height="60" rx="8" stroke="currentColor" stroke-width="3"/><path d="M40 25l15 15-15 15-15-15z" fill="currentColor"/></svg>',
                    'delay' => 0,
                ),
                array(
                    'title' => __('دعم فني بالعربي', 'ngs-designsystem'),
                    'description' => __('فريق دعم سعودي متخصص في الأجهزة الذكية يساعدك على مدار الساعة', 'ngs-designsystem'),
                    'icon' => '<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="40" cy="30" r="12" stroke="currentColor" stroke-width="3"/><path d="M20 65c0-11 9-20 20-20s20 9 20 20" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M25 55l5-5m25 5l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>',
                    'delay' => 100,
                ),
                array(
                    'title' => __('معتمد من معروف', 'ngs-designsystem'),
                    'description' => __('متجر معتمد من منصة معروف مع تقييمات موثقة من عملاء حقيقيين', 'ngs-designsystem'),
                    'icon' => '<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M40 15l-25 15v20c0 15 10 25 25 30 15-5 25-15 25-30V30z" stroke="currentColor" stroke-width="3"/><path d="M30 40l8 8 12-16" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'delay' => 200,
                ),
                array(
                    'title' => __('خدمة تركيب متاحة', 'ngs-designsystem'),
                    'description' => __('خدمات تركيب وإعداد احترافية في جميع أنحاء المملكة', 'ngs-designsystem'),
                    'icon' => '<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M25 40l-10 10 10 10m30-20l10 10-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><rect x="30" y="25" width="20" height="30" rx="2" stroke="currentColor" stroke-width="3"/><path d="M35 30h10m-10 5h10m-10 5h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
                    'delay' => 300,
                ),
            );

            foreach ($value_cards as $card) :
            ?>
            <div class="ngs-value-card" data-ngs-animate="fade-up" data-ngs-animate-delay="<?php echo esc_attr($card['delay']); ?>">
                <div class="ngs-value-card__icon">
                    <?php echo $card['icon']; ?>
                </div>
                <h3 class="ngs-value-card__title">
                    <?php echo esc_html($card['title']); ?>
                </h3>
                <p class="ngs-value-card__description">
                    <?php echo esc_html($card['description']); ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
