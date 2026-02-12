<?php
/**
 * Import history page template
 */

defined('ABSPATH') || exit;

$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

global $wpdb;
$table_name = $wpdb->prefix . 'neogen_imports';
$total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$total_pages = ceil($total / $per_page);

$imports = Neogen_Product_Importer::get_recent_imports($per_page, $offset);
?>

<div class="wrap neogen-importer-wrap">
    <h1>
        <span class="dashicons dashicons-list-view"></span>
        <?php _e('سجل الاستيرادات', 'neogen-importer'); ?>
    </h1>

    <div class="neogen-history-card">
        <div class="table-header">
            <div class="table-info">
                <?php printf(__('إجمالي %d سجل', 'neogen-importer'), $total); ?>
            </div>
            <div class="table-filters">
                <select id="filter-status">
                    <option value=""><?php _e('كل الحالات', 'neogen-importer'); ?></option>
                    <option value="completed"><?php _e('مكتمل', 'neogen-importer'); ?></option>
                    <option value="failed"><?php _e('فشل', 'neogen-importer'); ?></option>
                    <option value="processing"><?php _e('قيد المعالجة', 'neogen-importer'); ?></option>
                </select>
                <select id="filter-platform">
                    <option value=""><?php _e('كل المنصات', 'neogen-importer'); ?></option>
                    <option value="aliexpress">AliExpress</option>
                    <option value="amazon">Amazon</option>
                    <option value="alibaba">Alibaba</option>
                    <option value="unknown"><?php _e('أخرى', 'neogen-importer'); ?></option>
                </select>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="column-id"><?php _e('ID', 'neogen-importer'); ?></th>
                    <th class="column-source"><?php _e('المصدر', 'neogen-importer'); ?></th>
                    <th class="column-platform"><?php _e('المنصة', 'neogen-importer'); ?></th>
                    <th class="column-status"><?php _e('الحالة', 'neogen-importer'); ?></th>
                    <th class="column-product"><?php _e('المنتج', 'neogen-importer'); ?></th>
                    <th class="column-date"><?php _e('التاريخ', 'neogen-importer'); ?></th>
                    <th class="column-actions"><?php _e('إجراءات', 'neogen-importer'); ?></th>
                </tr>
            </thead>
            <tbody id="history-table-body">
                <?php if (empty($imports)): ?>
                    <tr>
                        <td colspan="7" class="no-items"><?php _e('لا توجد سجلات', 'neogen-importer'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($imports as $import): ?>
                        <tr data-id="<?php echo esc_attr($import->id); ?>">
                            <td class="column-id">#<?php echo esc_html($import->id); ?></td>
                            <td class="column-source">
                                <a href="<?php echo esc_url($import->source_url); ?>" target="_blank" class="source-link" title="<?php echo esc_attr($import->source_url); ?>">
                                    <?php
                                    $url_display = parse_url($import->source_url, PHP_URL_HOST);
                                    echo esc_html($url_display);
                                    ?>
                                </a>
                            </td>
                            <td class="column-platform">
                                <span class="platform-badge <?php echo esc_attr($import->source_platform); ?>">
                                    <?php echo esc_html(ucfirst($import->source_platform)); ?>
                                </span>
                            </td>
                            <td class="column-status">
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
                                <?php if ($import->status === 'failed' && $import->error_message): ?>
                                    <span class="error-tooltip" title="<?php echo esc_attr($import->error_message); ?>">
                                        <span class="dashicons dashicons-warning"></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="column-product">
                                <?php if ($import->product_id): ?>
                                    <a href="<?php echo get_edit_post_link($import->product_id); ?>" class="product-link">
                                        <?php echo esc_html(get_the_title($import->product_id)); ?>
                                    </a>
                                    <div class="product-actions">
                                        <a href="<?php echo get_permalink($import->product_id); ?>" target="_blank" title="<?php _e('عرض', 'neogen-importer'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </a>
                                        <a href="<?php echo get_edit_post_link($import->product_id); ?>" title="<?php _e('تعديل', 'neogen-importer'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="no-product">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="column-date">
                                <?php echo esc_html(date_i18n('Y/m/d', strtotime($import->created_at))); ?>
                                <br>
                                <small><?php echo esc_html(date_i18n('H:i', strtotime($import->created_at))); ?></small>
                            </td>
                            <td class="column-actions">
                                <button type="button" class="button button-small retry-import" data-id="<?php echo esc_attr($import->id); ?>" data-url="<?php echo esc_attr($import->source_url); ?>" title="<?php _e('إعادة المحاولة', 'neogen-importer'); ?>" <?php echo $import->status === 'completed' ? 'disabled' : ''; ?>>
                                    <span class="dashicons dashicons-update"></span>
                                </button>
                                <button type="button" class="button button-small delete-import" data-id="<?php echo esc_attr($import->id); ?>" title="<?php _e('حذف', 'neogen-importer'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf(__('%d عنصر', 'neogen-importer'), $total); ?>
                    </span>
                    <span class="pagination-links">
                        <?php if ($page > 1): ?>
                            <a class="first-page button" href="<?php echo add_query_arg('paged', 1); ?>">
                                <span class="screen-reader-text"><?php _e('الصفحة الأولى', 'neogen-importer'); ?></span>
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                            <a class="prev-page button" href="<?php echo add_query_arg('paged', $page - 1); ?>">
                                <span class="screen-reader-text"><?php _e('الصفحة السابقة', 'neogen-importer'); ?></span>
                                <span aria-hidden="true">&lsaquo;</span>
                            </a>
                        <?php endif; ?>

                        <span class="paging-input">
                            <span class="tablenav-paging-text">
                                <?php printf(__('%d من %d', 'neogen-importer'), $page, $total_pages); ?>
                            </span>
                        </span>

                        <?php if ($page < $total_pages): ?>
                            <a class="next-page button" href="<?php echo add_query_arg('paged', $page + 1); ?>">
                                <span class="screen-reader-text"><?php _e('الصفحة التالية', 'neogen-importer'); ?></span>
                                <span aria-hidden="true">&rsaquo;</span>
                            </a>
                            <a class="last-page button" href="<?php echo add_query_arg('paged', $total_pages); ?>">
                                <span class="screen-reader-text"><?php _e('الصفحة الأخيرة', 'neogen-importer'); ?></span>
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
