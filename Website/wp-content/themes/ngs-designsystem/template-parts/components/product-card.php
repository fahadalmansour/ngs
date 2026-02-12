<?php
/**
 * Product Card Component
 *
 * Expects $product global or passed via set_query_var
 *
 * @package NGS_DesignSystem
 */

// Get product object
$product = isset($product) ? $product : wc_get_product(get_the_ID());

if (!$product || !is_a($product, 'WC_Product')) {
    return;
}

// Get animation delay if set
$animate_delay = get_query_var('ngs_animate_delay', 0);

// Get product data
$product_id = $product->get_id();
$product_name = $product->get_name();
$product_permalink = $product->get_permalink();
$product_image_id = $product->get_image_id();
$is_on_sale = $product->is_on_sale();
$is_featured = $product->is_featured();
$is_in_stock = $product->is_in_stock();

// Get stock status
$stock_status = $product->get_stock_status();
$stock_status_text = '';
$stock_status_class = '';

switch ($stock_status) {
    case 'instock':
        $stock_status_text = __('In Stock', 'ngs-designsystem');
        $stock_status_class = 'in-stock';
        break;
    case 'outofstock':
        $stock_status_text = __('Out of Stock', 'ngs-designsystem');
        $stock_status_class = 'out-of-stock';
        break;
    case 'onbackorder':
        $stock_status_text = __('On Backorder', 'ngs-designsystem');
        $stock_status_class = 'on-backorder';
        break;
}

// Check for protocol attributes/taxonomies
$protocols = array();
$protocol_terms = get_the_terms($product_id, 'pa_protocol');
if ($protocol_terms && !is_wp_error($protocol_terms)) {
    foreach ($protocol_terms as $term) {
        $protocols[] = $term->name;
    }
}

// Check for 3D model (custom function - implement if needed)
$has_3d_model = function_exists('ngs_product_has_3d_model') ? ngs_product_has_3d_model($product_id) : false;
?>

<div class="ngs-product-card"
     data-product-id="<?php echo esc_attr($product_id); ?>"
     <?php if (!empty($protocols)) : ?>
        data-protocols="<?php echo esc_attr(strtolower(implode(',', $protocols))); ?>"
     <?php endif; ?>
     data-ngs-animate="fade-up"
     data-ngs-animate-delay="<?php echo esc_attr($animate_delay); ?>"
     role="listitem">

    <!-- Product Image -->
    <div class="ngs-product-card__image">
        <a href="<?php echo esc_url($product_permalink); ?>"
           aria-label="<?php echo esc_attr(sprintf(__('View %s details', 'ngs-designsystem'), $product_name)); ?>">
            <?php
            if ($product_image_id) {
                echo wp_get_attachment_image(
                    $product_image_id,
                    'medium',
                    false,
                    array(
                        'class' => 'ngs-product-card__img',
                        'alt' => $product_name,
                    )
                );
            } else {
                // Placeholder image
                ?>
                <div class="ngs-product-card__img-placeholder" role="img" aria-label="<?php echo esc_attr($product_name . ' ' . __('placeholder', 'ngs-designsystem')); ?>">
                    <svg width="288" height="288" viewBox="0 0 288 288" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="288" height="288" fill="#E5E7EB"/>
                        <circle cx="144" cy="144" r="40" fill="#9CA3AF"/>
                        <path d="M144 104v80M104 144h80" stroke="white" stroke-width="8" stroke-linecap="round"/>
                    </svg>
                </div>
                <?php
            }
            ?>
        </a>

        <!-- Badges on image -->
        <div class="ngs-product-card__image-badges">
            <?php if ($is_on_sale) : ?>
                <span class="ngs-product-card__badge ngs-product-card__badge--sale">
                    <?php esc_html_e('Sale', 'ngs-designsystem'); ?>
                </span>
            <?php endif; ?>

            <?php if ($is_featured) : ?>
                <span class="ngs-product-card__badge ngs-product-card__badge--new">
                    <?php esc_html_e('New', 'ngs-designsystem'); ?>
                </span>
            <?php endif; ?>

            <?php if ($has_3d_model) : ?>
                <span class="ngs-product-card__badge ngs-product-card__badge--ar"
                      aria-label="<?php esc_attr_e('3D model available', 'ngs-designsystem'); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M8 2l6 4v4l-6 4-6-4V6z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M8 8v6M2 6l6 2m0 0l6-2" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    AR
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Content -->
    <div class="ngs-product-card__content">
        <!-- Protocol Badges -->
        <?php if (!empty($protocols)) : ?>
            <div class="ngs-product-card__badges" role="list" aria-label="<?php esc_attr_e('Supported protocols', 'ngs-designsystem'); ?>">
                <?php foreach ($protocols as $protocol) : ?>
                    <span class="ngs-badge ngs-badge--sm ngs-badge--protocol" role="listitem">
                        <?php echo esc_html($protocol); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Product Title -->
        <h3 class="ngs-product-card__title">
            <a href="<?php echo esc_url($product_permalink); ?>">
                <?php echo esc_html($product_name); ?>
            </a>
        </h3>

        <!-- Price -->
        <div class="ngs-product-card__price" dir="ltr">
            <?php if ($is_on_sale) : ?>
                <span class="ngs-product-card__price-original">
                    <?php echo wp_kses_post($product->get_regular_price()); ?>
                    <span class="ngs-sr-only"><?php esc_html_e('SAR', 'ngs-designsystem'); ?></span>
                </span>
            <?php endif; ?>
            <span class="ngs-product-card__price-current">
                <?php
                if ($product->get_price()) {
                    echo wp_kses_post(wc_price($product->get_price()));
                } else {
                    echo wp_kses_post($product->get_price_html());
                }
                ?>
            </span>
        </div>

        <!-- Stock Status -->
        <div class="ngs-product-card__stock ngs-product-card__stock--<?php echo esc_attr($stock_status_class); ?>"
             role="status"
             aria-live="polite">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="6" cy="6" r="6" fill="currentColor"/>
            </svg>
            <?php echo esc_html($stock_status_text); ?>
        </div>

        <!-- Add to Cart Button -->
        <?php if ($is_in_stock) : ?>
            <button class="ngs-btn ngs-btn--primary ngs-btn--full ngs-product-card__cta ngs-btn-add-to-cart"
                    data-product-id="<?php echo esc_attr($product_id); ?>"
                    aria-label="<?php echo esc_attr(sprintf(__('Add %s to cart', 'ngs-designsystem'), $product_name)); ?>">
                <?php esc_html_e('أضف للسلة', 'ngs-designsystem'); ?>
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M1 1h3l1.68 8.39a2 2 0 0 0 2 1.61h7.72a2 2 0 0 0 2-1.61L17 4H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="7" cy="16" r="1" fill="currentColor"/>
                    <circle cx="14" cy="16" r="1" fill="currentColor"/>
                </svg>
            </button>
        <?php else : ?>
            <button class="ngs-btn ngs-btn--outline ngs-btn--full ngs-product-card__cta"
                    disabled
                    aria-label="<?php echo esc_attr(sprintf(__('%s is out of stock', 'ngs-designsystem'), $product_name)); ?>">
                <?php esc_html_e('Out of Stock', 'ngs-designsystem'); ?>
            </button>
        <?php endif; ?>

        <!-- Quick View Link (optional) -->
        <button class="ngs-product-card__quick-view"
                data-product-id="<?php echo esc_attr($product_id); ?>"
                aria-label="<?php echo esc_attr(sprintf(__('Quick view %s', 'ngs-designsystem'), $product_name)); ?>">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span class="ngs-sr-only"><?php esc_html_e('Quick View', 'ngs-designsystem'); ?></span>
        </button>
    </div>
</div>
