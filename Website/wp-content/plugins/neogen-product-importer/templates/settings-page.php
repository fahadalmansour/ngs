<?php
/**
 * Settings page template
 */

defined('ABSPATH') || exit;

$categories = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
]);

$api_key = get_option('neogen_importer_api_key');
// Generate API key if empty
if (empty($api_key)) {
    $api_key = 'neogen_' . wp_generate_password(24, false);
    update_option('neogen_importer_api_key', $api_key);
}
$callback_url = rest_url('neogen-importer/v1/callback');
?>

<div class="wrap neogen-importer-wrap">
    <h1>
        <span class="dashicons dashicons-admin-settings"></span>
        <?php _e('إعدادات الاستيراد', 'neogen-importer'); ?>
    </h1>

    <form method="post" action="options.php" class="neogen-settings-form">
        <?php settings_fields('neogen_importer_settings'); ?>

        <!-- N8N Settings -->
        <div class="neogen-settings-card">
            <h2>
                <span class="dashicons dashicons-admin-links"></span>
                <?php _e('إعدادات N8N', 'neogen-importer'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="n8n_webhook_url"><?php _e('رابط Webhook', 'neogen-importer'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="n8n_webhook_url" name="neogen_importer_n8n_webhook_url"
                            value="<?php echo esc_attr(get_option('neogen_importer_n8n_webhook_url')); ?>"
                            class="regular-text" placeholder="https://your-n8n-instance.com/webhook/..." />
                        <p class="description">
                            <?php _e('رابط Webhook من N8N لاستلام طلبات الاستيراد', 'neogen-importer'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <!-- N8N Integration Info -->
            <div class="n8n-info-box">
                <h4><?php _e('للاستخدام في N8N:', 'neogen-importer'); ?></h4>

                <div class="info-row">
                    <label><?php _e('Callback URL:', 'neogen-importer'); ?></label>
                    <code id="callback-url"><?php echo esc_html($callback_url); ?></code>
                    <button type="button" class="button button-small copy-btn" data-target="callback-url">
                        <span class="dashicons dashicons-clipboard"></span>
                    </button>
                </div>

                <div class="info-row">
                    <label><?php _e('API Key:', 'neogen-importer'); ?></label>
                    <code id="api-key"><?php echo esc_html($api_key); ?></code>
                    <button type="button" class="button button-small copy-btn" data-target="api-key">
                        <span class="dashicons dashicons-clipboard"></span>
                    </button>
                    <button type="button" class="button button-small" id="regenerate-api-key">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('جديد', 'neogen-importer'); ?>
                    </button>
                </div>

                <p class="description">
                    <?php _e('أضف هذه القيم في N8N workflow كـ HTTP Header:', 'neogen-importer'); ?>
                    <code>X-API-Key: <?php echo esc_html($api_key); ?></code>
                </p>
            </div>
        </div>

        <!-- Import Settings -->
        <div class="neogen-settings-card">
            <h2>
                <span class="dashicons dashicons-admin-tools"></span>
                <?php _e('إعدادات الاستيراد', 'neogen-importer'); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="default_category"><?php _e('الفئة الافتراضية', 'neogen-importer'); ?></label>
                    </th>
                    <td>
                        <select id="default_category" name="neogen_importer_default_category">
                            <option value=""><?php _e('-- بدون فئة --', 'neogen-importer'); ?></option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo esc_attr($cat->term_id); ?>"
                                    <?php selected(get_option('neogen_importer_default_category'), $cat->term_id); ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('الفئة التي ستُضاف للمنتجات إذا لم يتم تحديد فئة', 'neogen-importer'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="price_markup"><?php _e('نسبة الربح %', 'neogen-importer'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="price_markup" name="neogen_importer_price_markup"
                            value="<?php echo esc_attr(get_option('neogen_importer_price_markup', 30)); ?>"
                            min="0" max="500" step="1" class="small-text" />
                        <span>%</span>
                        <p class="description">
                            <?php _e('نسبة الزيادة على السعر الأصلي (مثال: 30 = سعر أصلي + 30%)', 'neogen-importer'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php _e('الترجمة التلقائية', 'neogen-importer'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="neogen_importer_auto_translate" value="yes"
                                <?php checked(get_option('neogen_importer_auto_translate', 'yes'), 'yes'); ?> />
                            <?php _e('ترجمة المحتوى للعربية تلقائياً (يتطلب N8N + AI)', 'neogen-importer'); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php _e('استيراد كمسودة', 'neogen-importer'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="neogen_importer_import_as_draft" value="yes"
                                <?php checked(get_option('neogen_importer_import_as_draft', 'yes'), 'yes'); ?> />
                            <?php _e('استيراد المنتجات كمسودة للمراجعة قبل النشر', 'neogen-importer'); ?>
                        </label>
                        <p class="description">
                            <?php _e('يُنصح بتفعيل هذا الخيار للتأكد من المحتوى قبل عرضه للعملاء', 'neogen-importer'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- N8N Workflow Template -->
        <div class="neogen-settings-card">
            <h2>
                <span class="dashicons dashicons-media-code"></span>
                <?php _e('قالب N8N Workflow', 'neogen-importer'); ?>
            </h2>

            <p><?php _e('استخدم هذا القالب لإنشاء workflow في N8N:', 'neogen-importer'); ?></p>

            <div class="workflow-template">
                <pre id="workflow-json">{
  "name": "Neogen Product Importer",
  "nodes": [
    {
      "name": "Webhook",
      "type": "n8n-nodes-base.webhook",
      "parameters": {
        "path": "product-import",
        "responseMode": "responseNode",
        "options": {}
      }
    },
    {
      "name": "HTTP Request - Fetch Page",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "url": "={{ $json.url }}",
        "options": {
          "timeout": 30000
        }
      }
    },
    {
      "name": "HTML Extract",
      "type": "n8n-nodes-base.html",
      "parameters": {
        "operation": "extractHtmlContent",
        "extractionValues": {
          "values": [
            {"key": "title", "cssSelector": "h1, .product-title"},
            {"key": "price", "cssSelector": ".price, [itemprop=price]"},
            {"key": "description", "cssSelector": ".description, #productDescription"},
            {"key": "images", "cssSelector": "img.product-image", "returnArray": true}
          ]
        }
      }
    },
    {
      "name": "AI Translation (Optional)",
      "type": "n8n-nodes-base.openAi",
      "parameters": {
        "operation": "text",
        "prompt": "Translate to Arabic: {{ $json.title }}\n{{ $json.description }}"
      }
    },
    {
      "name": "Callback to WordPress",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "method": "POST",
        "url": "<?php echo esc_js($callback_url); ?>",
        "authentication": "genericCredentialType",
        "genericAuthType": "httpHeaderAuth",
        "sendHeaders": true,
        "headerParameters": {
          "parameters": [
            {"name": "X-API-Key", "value": "<?php echo esc_js($api_key); ?>"}
          ]
        },
        "sendBody": true,
        "bodyParameters": {
          "parameters": [
            {"name": "import_id", "value": "={{ $json.import_id }}"},
            {"name": "title", "value": "={{ $json.title }}"},
            {"name": "description", "value": "={{ $json.description }}"},
            {"name": "price", "value": "={{ $json.price }}"},
            {"name": "images", "value": "={{ $json.images }}"}
          ]
        }
      }
    }
  ]
}</pre>
                <button type="button" class="button copy-workflow">
                    <span class="dashicons dashicons-clipboard"></span>
                    <?php _e('نسخ القالب', 'neogen-importer'); ?>
                </button>
            </div>
        </div>

        <?php submit_button(__('حفظ الإعدادات', 'neogen-importer')); ?>
    </form>
</div>
