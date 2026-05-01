<?php
/**
 * My Products template - Customer portal for registered products
 */
defined('ABSPATH') || exit;
?>

<div class="neogen-my-products">
    <h2>منتجاتي المسجلة</h2>
    <p class="description">سجّل رقم المنتج التسلسلي للحصول على الضمان والوصول إلى أدلة الاستخدام.</p>

    <?php if (empty($orders)) : ?>
        <div class="woocommerce-info">
            لا يوجد لديك طلبات حتى الآن. <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">تسوق الآن</a>
        </div>
    <?php else : ?>

        <!-- Registered Products Section -->
        <?php if (!empty($registered_serials)) : ?>
            <div class="registered-products-section">
                <h3>المنتجات المسجلة</h3>
                <div class="products-grid">
                    <?php foreach ($registered_serials as $serial) :
                        $product = wc_get_product($serial->product_id);
                        if (!$product) continue;
                        $manual_id = get_post_meta($serial->product_id, '_neogen_manual_id', true);
                        $warranty_status = strtotime($serial->warranty_end) > time() ? 'active' : 'expired';
                    ?>
                        <div class="product-card registered">
                            <div class="product-image">
                                <?php echo $product->get_image('thumbnail'); ?>
                            </div>
                            <div class="product-info">
                                <h4><?php echo esc_html($product->get_name()); ?></h4>
                                <div class="serial-badge">
                                    <span class="label">الرقم التسلسلي:</span>
                                    <span class="value"><?php echo esc_html($serial->serial_number); ?></span>
                                </div>
                                <div class="warranty-info <?php echo $warranty_status; ?>">
                                    <span class="label">الضمان:</span>
                                    <?php if ($warranty_status === 'active') : ?>
                                        <span class="status active">ساري حتى <?php echo date_i18n('Y/m/d', strtotime($serial->warranty_end)); ?></span>
                                    <?php else : ?>
                                        <span class="status expired">منتهي</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <?php if ($manual_id) : ?>
                                        <button class="button download-manual-btn" data-product-id="<?php echo $serial->product_id; ?>">
                                            📄 تحميل الدليل
                                        </button>
                                    <?php else : ?>
                                        <span class="no-manual">لا يوجد دليل متاح</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Unregistered Products Section -->
        <div class="unregistered-products-section">
            <h3>تسجيل منتج جديد</h3>
            <p>اختر طلبك ثم أدخل الرقم التسلسلي الموجود على المنتج أو العبوة:</p>

            <div class="orders-accordion">
                <?php foreach ($orders as $order) :
                    $order_id = $order->get_id();
                    $order_date = $order->get_date_created()->date_i18n('Y/m/d');
                ?>
                    <div class="order-item">
                        <div class="order-header" data-order-id="<?php echo $order_id; ?>">
                            <span class="order-number">طلب #<?php echo $order_id; ?></span>
                            <span class="order-date"><?php echo $order_date; ?></span>
                            <span class="order-status <?php echo $order->get_status(); ?>"><?php echo wc_get_order_status_name($order->get_status()); ?></span>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div class="order-products" style="display: none;">
                            <?php foreach ($order->get_items() as $item_id => $item) :
                                $product_id = $item->get_product_id();
                                $product = $item->get_product();
                                if (!$product) continue;

                                $qty = $item->get_quantity();
                                $manual_id = get_post_meta($product_id, '_neogen_manual_id', true);

                                // Check how many are already registered
                                global $wpdb;
                                $table_name = $wpdb->prefix . 'neogen_serials';
                                $registered_count = $wpdb->get_var($wpdb->prepare(
                                    "SELECT COUNT(*) FROM $table_name WHERE order_id = %d AND product_id = %d AND customer_id = %d",
                                    $order_id, $product_id, get_current_user_id()
                                ));

                                $remaining = $qty - $registered_count;
                            ?>
                                <div class="product-row">
                                    <div class="product-thumb">
                                        <?php echo $product->get_image('thumbnail'); ?>
                                    </div>
                                    <div class="product-details">
                                        <h4><?php echo esc_html($product->get_name()); ?></h4>
                                        <span class="qty">الكمية: <?php echo $qty; ?></span>
                                        <?php if ($registered_count > 0) : ?>
                                            <span class="registered-count">✓ مسجل: <?php echo $registered_count; ?>/<?php echo $qty; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-registration">
                                        <?php if ($remaining > 0) : ?>
                                            <div class="registration-form">
                                                <input type="text"
                                                       class="serial-input"
                                                       placeholder="أدخل الرقم التسلسلي"
                                                       data-order-id="<?php echo $order_id; ?>"
                                                       data-product-id="<?php echo $product_id; ?>" />
                                                <button class="button register-serial-btn">تسجيل</button>
                                            </div>
                                            <div class="registration-message"></div>
                                        <?php else : ?>
                                            <span class="all-registered">✓ تم تسجيل جميع المنتجات</span>
                                            <?php if ($manual_id) : ?>
                                                <button class="button download-manual-btn" data-product-id="<?php echo $product_id; ?>">
                                                    📄 تحميل الدليل
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<style>
/* Quick inline styles - also in assets/style.css */
.neogen-my-products h2 { margin-bottom: 10px; }
.neogen-my-products .description { color: #666; margin-bottom: 30px; }

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.product-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    gap: 15px;
}

.product-card.registered {
    border-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);
}

.product-card .product-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.product-card .product-info h4 {
    margin: 0 0 10px;
    font-size: 1rem;
}

.serial-badge {
    background: #f3f4f6;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    margin-bottom: 8px;
    display: inline-block;
}

.serial-badge .value {
    font-family: monospace;
    font-weight: bold;
    color: #1f1efb;
}

.warranty-info {
    font-size: 0.9rem;
    margin-bottom: 12px;
}

.warranty-info.active .status {
    color: #10b981;
    font-weight: 500;
}

.warranty-info.expired .status {
    color: #ef4444;
}

.orders-accordion .order-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 10px;
    overflow: hidden;
}

.order-header {
    background: #f9fafb;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
    transition: background 0.2s;
}

.order-header:hover {
    background: #f3f4f6;
}

.order-number {
    font-weight: 600;
}

.order-status {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    background: #e5e7eb;
}

.order-status.completed { background: #d1fae5; color: #065f46; }
.order-status.processing { background: #dbeafe; color: #1e40af; }

.toggle-icon {
    margin-right: auto;
    transition: transform 0.2s;
}

.order-header.open .toggle-icon {
    transform: rotate(180deg);
}

.order-products {
    padding: 20px;
    background: #fff;
}

.product-row {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f3f4f6;
}

.product-row:last-child {
    border-bottom: none;
}

.product-thumb img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

.product-details {
    flex: 1;
}

.product-details h4 {
    margin: 0 0 5px;
    font-size: 0.95rem;
}

.product-details .qty {
    color: #6b7280;
    font-size: 0.85rem;
}

.registered-count {
    color: #10b981;
    font-size: 0.85rem;
    display: block;
}

.registration-form {
    display: flex;
    gap: 8px;
}

.serial-input {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    width: 180px;
}

.register-serial-btn {
    background: #1f1efb !important;
    color: #fff !important;
    border: none !important;
    padding: 8px 16px !important;
    border-radius: 6px !important;
    cursor: pointer;
}

.download-manual-btn {
    background: #10b981 !important;
    color: #fff !important;
    border: none !important;
    margin-top: 10px;
}

.all-registered {
    color: #10b981;
    font-weight: 500;
}

.registration-message {
    margin-top: 8px;
    font-size: 0.85rem;
}

.registration-message.success { color: #10b981; }
.registration-message.error { color: #ef4444; }
</style>
