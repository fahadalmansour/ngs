<?php
/**
 * Admin page template
 */

defined('ABSPATH') || exit;

$categories = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
]);

$n8n_configured = !empty(get_option('neogen_importer_n8n_webhook_url'));
$stats = Neogen_Product_Importer::get_import_stats();
?>

<div class="wrap neogen-importer-wrap">
    <h1>
        <span class="dashicons dashicons-download"></span>
        <?php _e('استيراد المنتجات', 'neogen-importer'); ?>
    </h1>

    <!-- Stats Cards -->
    <div class="neogen-stats-grid">
        <div class="neogen-stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-chart-bar"></span>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html($stats['total']); ?></span>
                <span class="stat-label"><?php _e('إجمالي الاستيرادات', 'neogen-importer'); ?></span>
            </div>
        </div>
        <div class="neogen-stat-card success">
            <div class="stat-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html($stats['completed']); ?></span>
                <span class="stat-label"><?php _e('ناجح', 'neogen-importer'); ?></span>
            </div>
        </div>
        <div class="neogen-stat-card error">
            <div class="stat-icon">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html($stats['failed']); ?></span>
                <span class="stat-label"><?php _e('فشل', 'neogen-importer'); ?></span>
            </div>
        </div>
        <div class="neogen-stat-card warning">
            <div class="stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="stat-content">
                <span class="stat-number"><?php echo esc_html($stats['pending']); ?></span>
                <span class="stat-label"><?php _e('قيد المعالجة', 'neogen-importer'); ?></span>
            </div>
        </div>
    </div>

    <!-- Import Form -->
    <div class="neogen-import-card">
        <h2><?php _e('استيراد منتج جديد', 'neogen-importer'); ?></h2>

        <form id="neogen-import-form" class="neogen-import-form">
            <div class="form-row">
                <label for="product-url"><?php _e('رابط المنتج', 'neogen-importer'); ?></label>
                <input type="url" id="product-url" name="url" placeholder="https://www.aliexpress.com/item/..." required />
                <p class="description"><?php _e('أدخل رابط المنتج من أي موقع (AliExpress, Amazon, Alibaba, etc.)', 'neogen-importer'); ?></p>
            </div>

            <div class="form-row">
                <label for="product-category"><?php _e('الفئة', 'neogen-importer'); ?></label>
                <select id="product-category" name="category_id">
                    <option value=""><?php _e('-- اختر فئة --', 'neogen-importer'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat->term_id); ?>">
                            <?php echo esc_html($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row checkbox-row">
                <label>
                    <input type="checkbox" id="use-n8n" name="use_n8n" value="true" <?php echo $n8n_configured ? 'checked' : 'disabled'; ?> />
                    <?php _e('استخدام N8N للاستيراد المتقدم', 'neogen-importer'); ?>
                    <?php if (!$n8n_configured): ?>
                        <span class="not-configured">(<?php _e('غير مُعد', 'neogen-importer'); ?>)</span>
                    <?php endif; ?>
                </label>
                <p class="description"><?php _e('N8N يوفر استيراد أفضل للمواقع المحمية + ترجمة تلقائية', 'neogen-importer'); ?></p>
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('استيراد المنتج', 'neogen-importer'); ?>
                </button>
            </div>
        </form>

        <!-- Progress indicator -->
        <div id="import-progress" class="import-progress" style="display: none;">
            <div class="progress-spinner">
                <span class="spinner is-active"></span>
            </div>
            <div class="progress-message">
                <span id="progress-text"><?php _e('جاري الاستيراد...', 'neogen-importer'); ?></span>
            </div>
        </div>

        <!-- Result -->
        <div id="import-result" class="import-result" style="display: none;"></div>
    </div>

    <!-- Supported Platforms -->
    <div class="neogen-platforms-card">
        <h3><?php _e('المنصات المدعومة', 'neogen-importer'); ?></h3>
        <div class="platforms-grid">
            <div class="platform-item">
                <img src="https://ae01.alicdn.com/kf/Hcc51a239c0a14a3fb7b45bf45c3ded11I.png" alt="AliExpress" />
                <span>AliExpress</span>
            </div>
            <div class="platform-item">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon" />
                <span>Amazon</span>
            </div>
            <div class="platform-item">
                <img src="https://s.alicdn.com/@img/imgextra/i1/O1CN01AKUdEM1RHYQ9x5iXa_!!6000000002085-73-tps-167-39.ico" alt="Alibaba" />
                <span>Alibaba</span>
            </div>
            <div class="platform-item">
                <span class="dashicons dashicons-admin-site-alt3"></span>
                <span><?php _e('أي موقع آخر', 'neogen-importer'); ?></span>
            </div>
        </div>
    </div>

    <!-- Recent Imports -->
    <div class="neogen-recent-card">
        <h3><?php _e('آخر الاستيرادات', 'neogen-importer'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('المصدر', 'neogen-importer'); ?></th>
                    <th><?php _e('المنصة', 'neogen-importer'); ?></th>
                    <th><?php _e('الحالة', 'neogen-importer'); ?></th>
                    <th><?php _e('المنتج', 'neogen-importer'); ?></th>
                    <th><?php _e('التاريخ', 'neogen-importer'); ?></th>
                </tr>
            </thead>
            <tbody id="recent-imports-body">
                <?php
                $recent = Neogen_Product_Importer::get_recent_imports(5);
                if (empty($recent)):
                ?>
                    <tr>
                        <td colspan="5" class="no-items"><?php _e('لا توجد استيرادات بعد', 'neogen-importer'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent as $import): ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url($import->source_url); ?>" target="_blank" class="source-link">
                                    <?php echo esc_html(substr($import->source_url, 0, 50)); ?>...
                                </a>
                            </td>
                            <td>
                                <span class="platform-badge <?php echo esc_attr($import->source_platform); ?>">
                                    <?php echo esc_html(ucfirst($import->source_platform)); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($import->status); ?>">
                                    <?php
                                    $status_labels = [
                                        'pending' => __('قيد الانتظار', 'neogen-importer'),
                                        'processing' => __('قيد المعالجة', 'neogen-importer'),
                                        'completed' => __('مكتمل', 'neogen-importer'),
                                        'failed' => __('فشل', 'neogen-importer'),
                                    ];
                                    echo esc_html($status_labels[$import->status] ?? $import->status);
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($import->product_id): ?>
                                    <a href="<?php echo get_edit_post_link($import->product_id); ?>">
                                        <?php echo esc_html(get_the_title($import->product_id)); ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html(date_i18n('Y/m/d H:i', strtotime($import->created_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <p class="view-all">
            <a href="<?php echo admin_url('admin.php?page=neogen-importer-history'); ?>">
                <?php _e('عرض كل السجلات', 'neogen-importer'); ?> &rarr;
            </a>
        </p>
    </div>
</div>
