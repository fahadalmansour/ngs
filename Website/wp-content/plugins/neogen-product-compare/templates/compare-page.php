<?php
/**
 * Compare page template
 */

defined('ABSPATH') || exit;

$data = Neogen_Product_Compare::get_comparison_attributes($compare_list);
$products = $data['products'];
$attributes = $data['attributes'];

// Find best price
$best_price = PHP_INT_MAX;
$best_price_id = 0;
foreach ($products as $p) {
    if ($p['price'] > 0 && $p['price'] < $best_price) {
        $best_price = $p['price'];
        $best_price_id = $p['id'];
    }
}
?>

<div class="neogen-compare-page">
    <?php if (empty($products)): ?>
        <div class="compare-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 5H2v14h7M15 5h7v14h-7M9 12h6"/>
            </svg>
            <h2><?php _e('لا توجد منتجات للمقارنة', 'neogen-compare'); ?></h2>
            <p><?php _e('أضف منتجات للمقارنة من صفحة المتجر أو صفحات المنتجات', 'neogen-compare'); ?></p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn-shop">
                <?php _e('تصفح المنتجات', 'neogen-compare'); ?>
            </a>
        </div>
    <?php else: ?>
        <div class="compare-header">
            <h1><?php _e('مقارنة المنتجات', 'neogen-compare'); ?></h1>
            <button type="button" class="btn-clear-all" onclick="neogenClearCompare()">
                <?php _e('مسح الكل', 'neogen-compare'); ?>
            </button>
        </div>

        <div class="compare-table-wrapper">
            <table class="compare-table">
                <!-- Products Header -->
                <thead>
                    <tr class="products-row">
                        <th class="attribute-label"></th>
                        <?php foreach ($products as $product): ?>
                            <td class="product-cell">
                                <button type="button" class="remove-product" data-product-id="<?php echo esc_attr($product['id']); ?>">
                                    &times;
                                </button>
                                <a href="<?php echo esc_url($product['url']); ?>" class="product-image">
                                    <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>" />
                                </a>
                                <h3 class="product-name">
                                    <a href="<?php echo esc_url($product['url']); ?>">
                                        <?php echo esc_html($product['name']); ?>
                                    </a>
                                </h3>
                            </td>
                        <?php endforeach; ?>
                        <?php if (count($products) < 4): ?>
                            <td class="product-cell add-product-cell">
                                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="add-product-btn">
                                    <span class="plus-icon">+</span>
                                    <span><?php _e('أضف منتج', 'neogen-compare'); ?></span>
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody>
                    <!-- Price Row -->
                    <tr class="price-row">
                        <th class="attribute-label"><?php _e('السعر', 'neogen-compare'); ?></th>
                        <?php foreach ($products as $product): ?>
                            <td class="product-cell <?php echo $product['id'] === $best_price_id ? 'best-value' : ''; ?>">
                                <span class="price"><?php echo $product['price_html']; ?></span>
                                <?php if ($product['id'] === $best_price_id && count($products) > 1): ?>
                                    <span class="best-badge"><?php _e('أفضل سعر', 'neogen-compare'); ?></span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if (count($products) < 4): ?>
                            <td class="product-cell add-product-cell"></td>
                        <?php endif; ?>
                    </tr>

                    <!-- Rating Row -->
                    <tr>
                        <th class="attribute-label"><?php _e('التقييم', 'neogen-compare'); ?></th>
                        <?php foreach ($products as $product): ?>
                            <td class="product-cell">
                                <?php if ($product['rating'] > 0): ?>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $product['rating'] ? 'filled' : ''; ?>">&#9733;</span>
                                        <?php endfor; ?>
                                        <span class="review-count">(<?php echo $product['review_count']; ?>)</span>
                                    </div>
                                <?php else: ?>
                                    <span class="no-rating"><?php _e('لا توجد تقييمات', 'neogen-compare'); ?></span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if (count($products) < 4): ?>
                            <td class="product-cell add-product-cell"></td>
                        <?php endif; ?>
                    </tr>

                    <!-- Stock Status -->
                    <tr>
                        <th class="attribute-label"><?php _e('التوفر', 'neogen-compare'); ?></th>
                        <?php foreach ($products as $product): ?>
                            <td class="product-cell">
                                <?php if ($product['in_stock']): ?>
                                    <span class="in-stock"><?php _e('متوفر', 'neogen-compare'); ?></span>
                                <?php else: ?>
                                    <span class="out-of-stock"><?php _e('غير متوفر', 'neogen-compare'); ?></span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if (count($products) < 4): ?>
                            <td class="product-cell add-product-cell"></td>
                        <?php endif; ?>
                    </tr>

                    <!-- Attributes -->
                    <?php foreach ($attributes as $attr_name): ?>
                        <tr>
                            <th class="attribute-label"><?php echo esc_html($attr_name); ?></th>
                            <?php foreach ($products as $product): ?>
                                <td class="product-cell">
                                    <?php
                                    $value = $product['attributes'][$attr_name] ?? '';
                                    if ($value === 'yes' || $value === '1') {
                                        echo '<span class="check-yes">&#10003;</span>';
                                    } elseif ($value === 'no' || $value === '0') {
                                        echo '<span class="check-no">&#10007;</span>';
                                    } elseif ($value) {
                                        echo esc_html($value);
                                    } else {
                                        echo '<span class="no-value">-</span>';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if (count($products) < 4): ?>
                                <td class="product-cell add-product-cell"></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Add to Cart Row -->
                    <tr class="add-to-cart-row">
                        <th class="attribute-label"></th>
                        <?php foreach ($products as $product): ?>
                            <td class="product-cell">
                                <?php if ($product['in_stock']): ?>
                                    <a href="<?php echo esc_url($product['add_to_cart_url']); ?>" class="btn-add-to-cart">
                                        <?php _e('أضف للسلة', 'neogen-compare'); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="unavailable"><?php _e('غير متوفر', 'neogen-compare'); ?></span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if (count($products) < 4): ?>
                            <td class="product-cell add-product-cell"></td>
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Share Comparison -->
        <div class="compare-share">
            <span><?php _e('شارك المقارنة:', 'neogen-compare'); ?></span>
            <button type="button" class="share-btn" onclick="copyCompareLink()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                </svg>
                <?php _e('نسخ الرابط', 'neogen-compare'); ?>
            </button>
        </div>

        <script>
        function copyCompareLink() {
            var url = window.location.href;
            navigator.clipboard.writeText(url).then(function() {
                alert('<?php _e('تم نسخ الرابط!', 'neogen-compare'); ?>');
            });
        }

        function neogenClearCompare() {
            if (confirm('<?php _e('هل تريد مسح جميع المنتجات؟', 'neogen-compare'); ?>')) {
                jQuery.post(neogenCompare.ajax_url, {
                    action: 'neogen_clear_compare',
                    nonce: neogenCompare.nonce
                }, function() {
                    location.reload();
                });
            }
        }
        </script>
    <?php endif; ?>
</div>
