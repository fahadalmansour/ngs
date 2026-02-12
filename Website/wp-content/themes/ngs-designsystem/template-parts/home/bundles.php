<?php
/**
 * Product Bundles Section
 *
 * @package NGS_DesignSystem
 */
?>

<section class="ngs-bundles" data-ngs-section="bundles">
    <div class="ngs-bundles__container">
        <div class="ngs-bundles__header">
            <h2 class="ngs-bundles__title">
                <?php esc_html_e('باقات البيت الذكي', 'ngs-designsystem'); ?>
            </h2>
            <p class="ngs-bundles__subtitle">
                <?php esc_html_e('باقات جاهزة مع دعم التركيب', 'ngs-designsystem'); ?>
            </p>
        </div>

        <div class="ngs-bundles__grid">
            <?php
            $bundles = array(
                array(
                    'name' => __('Starter Kit', 'ngs-designsystem'),
                    'name_ar' => __('باقة المبتدئين', 'ngs-designsystem'),
                    'price' => 599,
                    'original_price' => 699,
                    'savings' => 15,
                    'items' => array(
                        __('جهاز Zigbee Hub', 'ngs-designsystem'),
                        __('مستشعر حركة', 'ngs-designsystem'),
                        __('مستشعر فتح باب', 'ngs-designsystem'),
                        __('مفتاح ذكي', 'ngs-designsystem'),
                        __('دعم تركيب مجاني', 'ngs-designsystem'),
                    ),
                    'product_id' => get_theme_mod('ngs_bundle_starter_id'),
                ),
                array(
                    'name' => __('Security Kit', 'ngs-designsystem'),
                    'name_ar' => __('باقة الأمان', 'ngs-designsystem'),
                    'price' => 899,
                    'original_price' => 1099,
                    'savings' => 18,
                    'items' => array(
                        __('كاميرا ذكية (2x)', 'ngs-designsystem'),
                        __('جرس باب ذكي', 'ngs-designsystem'),
                        __('مستشعر حركة (3x)', 'ngs-designsystem'),
                        __('سيرين إنذار', 'ngs-designsystem'),
                        __('إعداد كامل مجاني', 'ngs-designsystem'),
                    ),
                    'product_id' => get_theme_mod('ngs_bundle_security_id'),
                ),
                array(
                    'name' => __('Climate Kit', 'ngs-designsystem'),
                    'name_ar' => __('باقة التحكم بالمناخ', 'ngs-designsystem'),
                    'price' => 749,
                    'original_price' => 899,
                    'savings' => 17,
                    'items' => array(
                        __('ثيرموستات ذكي', 'ngs-designsystem'),
                        __('مستشعر حرارة ورطوبة (3x)', 'ngs-designsystem'),
                        __('مفتاح مكيف ذكي (2x)', 'ngs-designsystem'),
                        __('مروحة ذكية', 'ngs-designsystem'),
                        __('برمجة مجانية', 'ngs-designsystem'),
                    ),
                    'product_id' => get_theme_mod('ngs_bundle_climate_id'),
                ),
            );

            foreach ($bundles as $index => $bundle) :
                $product = null;
                if (function_exists('wc_get_product') && $bundle['product_id']) {
                    $product = wc_get_product($bundle['product_id']);
                }
            ?>
            <div class="ngs-bundle-card" data-ngs-animate="fade-up" data-ngs-animate-delay="<?php echo esc_attr($index * 100); ?>">
                <div class="ngs-bundle-card__image">
                    <?php
                    if ($product && $product->get_image_id()) {
                        echo wp_get_attachment_image(
                            $product->get_image_id(),
                            'medium',
                            false,
                            array(
                                'class' => 'ngs-bundle-card__img',
                                'alt' => $bundle['name'],
                            )
                        );
                    } else {
                        // Placeholder
                        ?>
                        <div class="ngs-bundle-card__img-placeholder" role="img" aria-label="<?php echo esc_attr($bundle['name'] . ' ' . __('placeholder', 'ngs-designsystem')); ?>">
                            <svg width="384" height="288" viewBox="0 0 384 288" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="384" height="288" fill="#E5E7EB"/>
                                <path d="M192 96l48 48-48 48-48-48z" fill="#9CA3AF"/>
                                <rect x="120" y="180" width="144" height="8" rx="4" fill="#9CA3AF"/>
                                <rect x="140" y="200" width="104" height="8" rx="4" fill="#D1D5DB"/>
                            </svg>
                        </div>
                        <?php
                    }
                    ?>

                    <span class="ngs-bundle-card__savings-badge">
                        <?php
                        /* translators: %d: Savings percentage */
                        printf(esc_html__('Save %d%%', 'ngs-designsystem'), $bundle['savings']);
                        ?>
                    </span>
                </div>

                <div class="ngs-bundle-card__content">
                    <h3 class="ngs-bundle-card__title">
                        <?php echo esc_html($bundle['name_ar']); ?>
                    </h3>

                    <div class="ngs-bundle-card__pricing">
                        <span class="ngs-bundle-card__price-original" dir="ltr">
                            <?php
                            /* translators: %d: Original price */
                            printf(esc_html__('SAR %d', 'ngs-designsystem'), $bundle['original_price']);
                            ?>
                        </span>
                        <span class="ngs-bundle-card__price-current" dir="ltr">
                            <?php
                            /* translators: %d: Current price */
                            printf(esc_html__('SAR %d', 'ngs-designsystem'), $bundle['price']);
                            ?>
                        </span>
                    </div>

                    <ul class="ngs-bundle-card__items" role="list" aria-label="<?php esc_attr_e('Included items', 'ngs-designsystem'); ?>">
                        <?php foreach ($bundle['items'] as $item) : ?>
                            <li class="ngs-bundle-card__item" role="listitem">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <circle cx="8" cy="8" r="8" fill="#10B981"/>
                                    <path d="M5 8l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <?php echo esc_html($item); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($product) : ?>
                        <button class="ngs-btn ngs-btn--primary ngs-btn--full ngs-btn-add-to-cart"
                                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Add %s to cart', 'ngs-designsystem'), $bundle['name_ar'])); ?>">
                            <?php esc_html_e('أضف للسلة', 'ngs-designsystem'); ?>
                        </button>
                    <?php else : ?>
                        <a href="<?php echo esc_url(home_url('/shop/bundles/')); ?>"
                           class="ngs-btn ngs-btn--primary ngs-btn--full">
                            <?php esc_html_e('View Details', 'ngs-designsystem'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
