<?php
/**
 * Admin Serials Management Page
 */
defined('ABSPATH') || exit;

// Get all products for dropdown
$products = wc_get_products(['limit' => -1, 'status' => 'publish']);
?>

<div class="wrap">
    <h1>إدارة الأرقام التسلسلية</h1>

    <div class="neogen-admin-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px;">

        <!-- Add Serial Form -->
        <div class="add-serial-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;">إضافة رقم تسلسلي</h2>
            <form method="post">
                <?php wp_nonce_field('neogen_add_serial'); ?>

                <table class="form-table">
                    <tr>
                        <th><label for="serial_number">الرقم التسلسلي *</label></th>
                        <td>
                            <input type="text" name="serial_number" id="serial_number" class="regular-text" required
                                   placeholder="مثال: NGS-2024-001234" />
                            <p class="description">الرقم التسلسلي الموجود على المنتج</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="product_id">المنتج *</label></th>
                        <td>
                            <select name="product_id" id="product_id" class="regular-text" required>
                                <option value="">-- اختر المنتج --</option>
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?php echo $product->get_id(); ?>">
                                        <?php echo esc_html($product->get_name()); ?> (#<?php echo $product->get_id(); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="order_id">رقم الطلب</label></th>
                        <td>
                            <input type="number" name="order_id" id="order_id" class="regular-text"
                                   placeholder="اتركه فارغاً إذا لم يُباع بعد" />
                            <p class="description">اختياري - إذا تم بيع المنتج</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="warranty_months">مدة الضمان</label></th>
                        <td>
                            <select name="warranty_months" id="warranty_months">
                                <option value="12">12 شهر (سنة)</option>
                                <option value="24">24 شهر (سنتين)</option>
                                <option value="6">6 أشهر</option>
                                <option value="3">3 أشهر</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="neogen_add_serial" class="button button-primary">
                        إضافة الرقم التسلسلي
                    </button>
                </p>
            </form>
        </div>

        <!-- Serials List -->
        <div class="serials-list-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;">الأرقام التسلسلية المسجلة</h2>

            <?php if (empty($serials)) : ?>
                <p>لا توجد أرقام تسلسلية مسجلة حتى الآن.</p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>الرقم التسلسلي</th>
                            <th>المنتج</th>
                            <th>الطلب</th>
                            <th>العميل</th>
                            <th>الضمان</th>
                            <th>الحالة</th>
                            <th>مسجل بواسطة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serials as $serial) :
                            $product = wc_get_product($serial->product_id);
                            $customer = $serial->customer_id ? get_user_by('id', $serial->customer_id) : null;
                            $warranty_status = $serial->warranty_end && strtotime($serial->warranty_end) > time() ? 'active' : 'expired';
                        ?>
                            <tr>
                                <td>
                                    <strong style="font-family: monospace;"><?php echo esc_html($serial->serial_number); ?></strong>
                                </td>
                                <td>
                                    <?php if ($product) : ?>
                                        <a href="<?php echo get_edit_post_link($serial->product_id); ?>">
                                            <?php echo esc_html($product->get_name()); ?>
                                        </a>
                                    <?php else : ?>
                                        <span style="color: #999;">محذوف</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($serial->order_id) : ?>
                                        <a href="<?php echo admin_url('post.php?post=' . $serial->order_id . '&action=edit'); ?>">
                                            #<?php echo $serial->order_id; ?>
                                        </a>
                                    <?php else : ?>
                                        <span style="color: #999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($customer) : ?>
                                        <a href="<?php echo admin_url('user-edit.php?user_id=' . $serial->customer_id); ?>">
                                            <?php echo esc_html($customer->display_name); ?>
                                        </a>
                                    <?php else : ?>
                                        <span style="color: #999;">غير مسجل</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($serial->warranty_end) : ?>
                                        <?php if ($warranty_status === 'active') : ?>
                                            <span style="color: #10b981;">✓ حتى <?php echo date('Y/m/d', strtotime($serial->warranty_end)); ?></span>
                                        <?php else : ?>
                                            <span style="color: #ef4444;">✗ انتهى <?php echo date('Y/m/d', strtotime($serial->warranty_end)); ?></span>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <span style="color: #999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $serial->status; ?>" style="
                                        padding: 3px 8px;
                                        border-radius: 4px;
                                        font-size: 0.85em;
                                        background: <?php echo $serial->status === 'active' ? '#d1fae5' : '#fee2e2'; ?>;
                                        color: <?php echo $serial->status === 'active' ? '#065f46' : '#991b1b'; ?>;
                                    ">
                                        <?php echo $serial->status === 'active' ? 'نشط' : 'غير نشط'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $serial->registered_by === 'admin' ? 'المتجر' : 'العميل'; ?>
                                    <br>
                                    <small style="color: #999;"><?php echo date('Y/m/d', strtotime($serial->registration_date)); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

    <!-- Stats Summary -->
    <div class="stats-cards" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 30px;">
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_serials';
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $registered = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE customer_id IS NOT NULL");
        $active_warranty = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE warranty_end > NOW()");
        $expired = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE warranty_end <= NOW()");
        ?>
        <div style="background: #fff; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold; color: #1f1efb;"><?php echo $total; ?></div>
            <div style="color: #666;">إجمالي الأرقام</div>
        </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold; color: #10b981;"><?php echo $registered; ?></div>
            <div style="color: #666;">مسجل لعملاء</div>
        </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold; color: #059669;"><?php echo $active_warranty; ?></div>
            <div style="color: #666;">ضمان ساري</div>
        </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2em; font-weight: bold; color: #ef4444;"><?php echo $expired; ?></div>
            <div style="color: #666;">ضمان منتهي</div>
        </div>
    </div>
</div>
