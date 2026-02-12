<?php
/**
 * Plugin Name: Neogen Product Importer
 * Description: Import products from any URL (Amazon, AliExpress, etc.) via N8N automation
 * Version: 1.0.0
 * Author: neogen
 * Text Domain: neogen-importer
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

defined('ABSPATH') || exit;

// Plugin constants
define('NEOGEN_IMPORTER_VERSION', '1.0.0');
define('NEOGEN_IMPORTER_PATH', plugin_dir_path(__FILE__));
define('NEOGEN_IMPORTER_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Neogen_Product_Importer {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Activation
        register_activation_hook(__FILE__, [$this, 'activate']);

        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Admin scripts
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        // REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // AJAX handlers
        add_action('wp_ajax_neogen_import_product', [$this, 'ajax_import_product']);
        add_action('wp_ajax_neogen_get_import_status', [$this, 'ajax_get_import_status']);
        add_action('wp_ajax_neogen_delete_import', [$this, 'ajax_delete_import']);
        add_action('wp_ajax_neogen_regenerate_api_key', [$this, 'ajax_regenerate_api_key']);

        // Settings
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Plugin activation - create database table
     */
    public function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'neogen_imports';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            source_url text NOT NULL,
            source_platform varchar(50) DEFAULT 'unknown',
            product_id bigint(20) DEFAULT NULL,
            status varchar(20) DEFAULT 'pending',
            original_data longtext,
            processed_data longtext,
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Default settings
        add_option('neogen_importer_n8n_webhook_url', '');
        add_option('neogen_importer_default_category', '');
        add_option('neogen_importer_price_markup', 30);
        add_option('neogen_importer_auto_translate', 'yes');
        add_option('neogen_importer_import_as_draft', 'yes');
        add_option('neogen_importer_api_key', wp_generate_password(32, false));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('neogen_importer_settings', 'neogen_importer_n8n_webhook_url');
        register_setting('neogen_importer_settings', 'neogen_importer_default_category');
        register_setting('neogen_importer_settings', 'neogen_importer_price_markup');
        register_setting('neogen_importer_settings', 'neogen_importer_auto_translate');
        register_setting('neogen_importer_settings', 'neogen_importer_import_as_draft');
    }

    /**
     * AJAX: Regenerate API key
     */
    public function ajax_regenerate_api_key() {
        check_ajax_referer('neogen_importer_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $new_key = 'neogen_' . wp_generate_password(24, false);
        update_option('neogen_importer_api_key', $new_key);

        wp_send_json_success(['api_key' => $new_key]);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Product Importer', 'neogen-importer'),
            __('Product Importer', 'neogen-importer'),
            'manage_woocommerce',
            'neogen-importer',
            [$this, 'admin_page'],
            'dashicons-download',
            57
        );

        add_submenu_page(
            'neogen-importer',
            __('Import History', 'neogen-importer'),
            __('Import History', 'neogen-importer'),
            'manage_woocommerce',
            'neogen-importer-history',
            [$this, 'history_page']
        );

        add_submenu_page(
            'neogen-importer',
            __('Settings', 'neogen-importer'),
            __('Settings', 'neogen-importer'),
            'manage_woocommerce',
            'neogen-importer-settings',
            [$this, 'settings_page']
        );
    }

    /**
     * Admin page
     */
    public function admin_page() {
        include NEOGEN_IMPORTER_PATH . 'templates/admin-page.php';
    }

    /**
     * History page
     */
    public function history_page() {
        include NEOGEN_IMPORTER_PATH . 'templates/history-page.php';
    }

    /**
     * Settings page
     */
    public function settings_page() {
        include NEOGEN_IMPORTER_PATH . 'templates/settings-page.php';
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'neogen-importer') === false) {
            return;
        }

        wp_enqueue_style(
            'neogen-importer-admin',
            NEOGEN_IMPORTER_URL . 'assets/admin.css',
            [],
            NEOGEN_IMPORTER_VERSION
        );

        wp_enqueue_script(
            'neogen-importer-admin',
            NEOGEN_IMPORTER_URL . 'assets/admin.js',
            ['jquery'],
            NEOGEN_IMPORTER_VERSION,
            true
        );

        wp_localize_script('neogen-importer-admin', 'neogenImporter', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('neogen_importer_nonce'),
            'callback_url' => rest_url('neogen-importer/v1/callback'),
            'strings' => [
                'importing' => __('جاري الاستيراد...', 'neogen-importer'),
                'success' => __('تم استيراد المنتج بنجاح!', 'neogen-importer'),
                'error' => __('حدث خطأ أثناء الاستيراد', 'neogen-importer'),
                'confirm_delete' => __('هل أنت متأكد من حذف هذا السجل؟', 'neogen-importer'),
            ]
        ]);
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Callback endpoint for N8N to send scraped data
        register_rest_route('neogen-importer/v1', '/callback', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_n8n_callback'],
            'permission_callback' => [$this, 'verify_api_key'],
        ]);

        // Direct scrape endpoint (for simple cases without N8N)
        register_rest_route('neogen-importer/v1', '/scrape', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_direct_scrape'],
            'permission_callback' => [$this, 'verify_api_key'],
        ]);
    }

    /**
     * Verify API key (timing-safe comparison)
     */
    public function verify_api_key($request) {
        $api_key = $request->get_header('X-API-Key');
        $stored_key = get_option('neogen_importer_api_key');

        if (empty($api_key) || empty($stored_key)) {
            return false;
        }

        return hash_equals($stored_key, $api_key);
    }

    /**
     * Handle N8N callback with scraped data
     */
    public function handle_n8n_callback($request) {
        $data = $request->get_json_params();

        if (empty($data['import_id'])) {
            return new WP_Error('missing_import_id', 'Import ID is required', ['status' => 400]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_imports';

        // Get import record
        $import = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $data['import_id']
        ));

        if (!$import) {
            return new WP_Error('import_not_found', 'Import record not found', ['status' => 404]);
        }

        // Check if N8N returned an error
        if (!empty($data['error'])) {
            $wpdb->update($table_name, [
                'status' => 'failed',
                'error_message' => $data['error'],
            ], ['id' => $data['import_id']]);

            return ['success' => false, 'message' => $data['error']];
        }

        // Process the scraped data
        $product_data = $this->process_scraped_data($data);

        // Create WooCommerce product
        $product_id = $this->create_wc_product($product_data);

        if (is_wp_error($product_id)) {
            $wpdb->update($table_name, [
                'status' => 'failed',
                'error_message' => $product_id->get_error_message(),
            ], ['id' => $data['import_id']]);

            return ['success' => false, 'message' => $product_id->get_error_message()];
        }

        // Update import record
        $wpdb->update($table_name, [
            'status' => 'completed',
            'product_id' => $product_id,
            'processed_data' => json_encode($product_data),
        ], ['id' => $data['import_id']]);

        return [
            'success' => true,
            'product_id' => $product_id,
            'product_url' => get_edit_post_link($product_id, 'raw'),
        ];
    }

    /**
     * Handle direct scrape (basic URL fetching without N8N)
     */
    public function handle_direct_scrape($request) {
        $url = $request->get_param('url');

        if (empty($url)) {
            return new WP_Error('missing_url', 'URL is required', ['status' => 400]);
        }

        // Detect platform
        $platform = $this->detect_platform($url);

        // Try to scrape basic info
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $html = wp_remote_retrieve_body($response);
        $data = $this->extract_product_data($html, $platform);

        return [
            'success' => true,
            'platform' => $platform,
            'data' => $data,
        ];
    }

    /**
     * AJAX: Import product
     */
    public function ajax_import_product() {
        check_ajax_referer('neogen_importer_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $url = sanitize_url($_POST['url']);
        $category_id = intval($_POST['category_id']);
        $use_n8n = isset($_POST['use_n8n']) && $_POST['use_n8n'] === 'true';

        if (empty($url)) {
            wp_send_json_error(['message' => 'يرجى إدخال رابط المنتج']);
        }

        // Detect platform
        $platform = $this->detect_platform($url);

        // Create import record
        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_imports';

        $wpdb->insert($table_name, [
            'source_url' => $url,
            'source_platform' => $platform,
            'status' => 'pending',
            'original_data' => json_encode(['category_id' => $category_id]),
        ]);

        $import_id = $wpdb->insert_id;

        // If N8N is configured and enabled
        $n8n_webhook = get_option('neogen_importer_n8n_webhook_url');

        if ($use_n8n && !empty($n8n_webhook)) {
            // Send to N8N
            $response = wp_remote_post($n8n_webhook, [
                'timeout' => 10,
                'body' => json_encode([
                    'import_id' => $import_id,
                    'url' => $url,
                    'platform' => $platform,
                    'category_id' => $category_id,
                    'callback_url' => rest_url('neogen-importer/v1/callback'),
                    'api_key' => get_option('neogen_importer_api_key'),
                    'settings' => [
                        'price_markup' => get_option('neogen_importer_price_markup', 30),
                        'auto_translate' => get_option('neogen_importer_auto_translate', 'yes'),
                        'import_as_draft' => get_option('neogen_importer_import_as_draft', 'yes'),
                    ],
                ]),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            if (is_wp_error($response)) {
                $wpdb->update($table_name, [
                    'status' => 'failed',
                    'error_message' => $response->get_error_message(),
                ], ['id' => $import_id]);

                wp_send_json_error(['message' => 'فشل الاتصال بـ N8N: ' . $response->get_error_message()]);
            }

            $wpdb->update($table_name, ['status' => 'processing'], ['id' => $import_id]);

            wp_send_json_success([
                'message' => 'تم إرسال الطلب إلى N8N',
                'import_id' => $import_id,
                'status' => 'processing',
            ]);
        } else {
            // Direct import (basic scraping)
            $wpdb->update($table_name, ['status' => 'processing'], ['id' => $import_id]);

            $result = $this->direct_import($url, $platform, $category_id);

            if (is_wp_error($result)) {
                $wpdb->update($table_name, [
                    'status' => 'failed',
                    'error_message' => $result->get_error_message(),
                ], ['id' => $import_id]);

                wp_send_json_error(['message' => $result->get_error_message()]);
            }

            $wpdb->update($table_name, [
                'status' => 'completed',
                'product_id' => $result['product_id'],
                'processed_data' => json_encode($result['data']),
            ], ['id' => $import_id]);

            wp_send_json_success([
                'message' => 'تم استيراد المنتج بنجاح!',
                'import_id' => $import_id,
                'product_id' => $result['product_id'],
                'product_url' => get_edit_post_link($result['product_id'], 'raw'),
                'status' => 'completed',
            ]);
        }
    }

    /**
     * AJAX: Get import status
     */
    public function ajax_get_import_status() {
        check_ajax_referer('neogen_importer_nonce', 'nonce');

        $import_id = intval($_POST['import_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_imports';

        $import = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $import_id
        ));

        if (!$import) {
            wp_send_json_error(['message' => 'السجل غير موجود']);
        }

        $response = [
            'status' => $import->status,
            'product_id' => $import->product_id,
        ];

        if ($import->status === 'completed' && $import->product_id) {
            $response['product_url'] = get_edit_post_link($import->product_id, 'raw');
            $response['product_title'] = get_the_title($import->product_id);
        }

        if ($import->status === 'failed') {
            $response['error'] = $import->error_message;
        }

        wp_send_json_success($response);
    }

    /**
     * AJAX: Delete import record
     */
    public function ajax_delete_import() {
        check_ajax_referer('neogen_importer_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $import_id = intval($_POST['import_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_imports';

        $wpdb->delete($table_name, ['id' => $import_id]);

        wp_send_json_success(['message' => 'تم الحذف بنجاح']);
    }

    /**
     * Detect platform from URL
     */
    private function detect_platform($url) {
        $host = parse_url($url, PHP_URL_HOST);

        $platforms = [
            'aliexpress' => ['aliexpress.com', 'aliexpress.ru', 'aliexpress.us'],
            'amazon' => ['amazon.com', 'amazon.sa', 'amazon.ae', 'amazon.co.uk', 'amazon.de'],
            'alibaba' => ['alibaba.com', '1688.com'],
            'banggood' => ['banggood.com'],
            'gearbest' => ['gearbest.com'],
            'ebay' => ['ebay.com', 'ebay.co.uk'],
            'taobao' => ['taobao.com', 'tmall.com'],
        ];

        foreach ($platforms as $platform => $domains) {
            foreach ($domains as $domain) {
                if (strpos($host, $domain) !== false) {
                    return $platform;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Direct import (basic scraping)
     */
    private function direct_import($url, $platform, $category_id) {
        // Fetch the page
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $html = wp_remote_retrieve_body($response);

        if (empty($html)) {
            return new WP_Error('empty_response', 'لم يتم استلام محتوى من الصفحة');
        }

        // Extract product data
        $data = $this->extract_product_data($html, $platform);

        if (empty($data['title'])) {
            return new WP_Error('extraction_failed', 'فشل استخراج بيانات المنتج. قد تحتاج لاستخدام N8N للمواقع المحمية.');
        }

        // Apply price markup
        if (!empty($data['price'])) {
            $markup = get_option('neogen_importer_price_markup', 30);
            $data['price'] = $data['price'] * (1 + $markup / 100);
        }

        // Create product
        $product_id = $this->create_wc_product([
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'short_description' => $data['short_description'] ?? '',
            'price' => $data['price'] ?? '',
            'images' => $data['images'] ?? [],
            'category_id' => $category_id,
            'source_url' => $url,
        ]);

        if (is_wp_error($product_id)) {
            return $product_id;
        }

        return [
            'product_id' => $product_id,
            'data' => $data,
        ];
    }

    /**
     * Extract product data from HTML
     */
    private function extract_product_data($html, $platform) {
        $data = [
            'title' => '',
            'description' => '',
            'price' => '',
            'images' => [],
        ];

        // Use DOMDocument for parsing
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($doc);

        // Platform-specific extraction
        switch ($platform) {
            case 'aliexpress':
                $data = $this->extract_aliexpress($xpath, $html);
                break;
            case 'amazon':
                $data = $this->extract_amazon($xpath, $html);
                break;
            default:
                $data = $this->extract_generic($xpath, $html);
        }

        // Fallback to meta tags
        if (empty($data['title'])) {
            // Try og:title
            $og_title = $xpath->query('//meta[@property="og:title"]/@content');
            if ($og_title->length > 0) {
                $data['title'] = $og_title->item(0)->nodeValue;
            }
        }

        if (empty($data['description'])) {
            // Try og:description
            $og_desc = $xpath->query('//meta[@property="og:description"]/@content');
            if ($og_desc->length > 0) {
                $data['description'] = $og_desc->item(0)->nodeValue;
            }
        }

        if (empty($data['images'])) {
            // Try og:image
            $og_image = $xpath->query('//meta[@property="og:image"]/@content');
            if ($og_image->length > 0) {
                $data['images'][] = $og_image->item(0)->nodeValue;
            }
        }

        libxml_clear_errors();

        return $data;
    }

    /**
     * Extract AliExpress data
     */
    private function extract_aliexpress($xpath, $html) {
        $data = [
            'title' => '',
            'description' => '',
            'price' => '',
            'images' => [],
        ];

        // Title
        $title = $xpath->query('//h1[contains(@class, "title")]');
        if ($title->length > 0) {
            $data['title'] = trim($title->item(0)->nodeValue);
        }

        // Try to find price in JSON data
        if (preg_match('/"formatedAmount":"([^"]+)"/', $html, $matches)) {
            $data['price'] = floatval(preg_replace('/[^0-9.]/', '', $matches[1]));
        }

        // Images from JSON
        if (preg_match_all('/"imagePathList":\s*\[([^\]]+)\]/', $html, $matches)) {
            preg_match_all('/"([^"]+\.jpg[^"]*)"/', $matches[1][0], $img_matches);
            if (!empty($img_matches[1])) {
                $data['images'] = array_slice($img_matches[1], 0, 5);
            }
        }

        return $data;
    }

    /**
     * Extract Amazon data
     */
    private function extract_amazon($xpath, $html) {
        $data = [
            'title' => '',
            'description' => '',
            'price' => '',
            'images' => [],
        ];

        // Title
        $title = $xpath->query('//span[@id="productTitle"]');
        if ($title->length > 0) {
            $data['title'] = trim($title->item(0)->nodeValue);
        }

        // Price
        $price = $xpath->query('//span[contains(@class, "a-price-whole")]');
        if ($price->length > 0) {
            $data['price'] = floatval(preg_replace('/[^0-9.]/', '', $price->item(0)->nodeValue));
        }

        // Description
        $desc = $xpath->query('//div[@id="productDescription"]//p');
        if ($desc->length > 0) {
            $data['description'] = trim($desc->item(0)->nodeValue);
        }

        // Feature bullets
        $bullets = $xpath->query('//div[@id="feature-bullets"]//li//span');
        $features = [];
        foreach ($bullets as $bullet) {
            $features[] = trim($bullet->nodeValue);
        }
        if (!empty($features)) {
            $data['description'] .= "\n\n" . implode("\n", array_map(function($f) { return "• " . $f; }, $features));
        }

        // Images
        if (preg_match_all('/"hiRes":"([^"]+)"/', $html, $matches)) {
            $data['images'] = array_unique(array_slice($matches[1], 0, 5));
        }

        return $data;
    }

    /**
     * Extract generic data
     */
    private function extract_generic($xpath, $html) {
        $data = [
            'title' => '',
            'description' => '',
            'price' => '',
            'images' => [],
        ];

        // Try common selectors for title
        $title_selectors = [
            '//h1[contains(@class, "product")]',
            '//h1[contains(@class, "title")]',
            '//h1[@itemprop="name"]',
            '//h1',
        ];

        foreach ($title_selectors as $selector) {
            $title = $xpath->query($selector);
            if ($title->length > 0) {
                $data['title'] = trim($title->item(0)->nodeValue);
                break;
            }
        }

        // Try common selectors for price
        $price_selectors = [
            '//*[contains(@class, "price")]',
            '//*[@itemprop="price"]',
        ];

        foreach ($price_selectors as $selector) {
            $price = $xpath->query($selector);
            if ($price->length > 0) {
                $price_text = $price->item(0)->nodeValue;
                if (preg_match('/[\d,.]+/', $price_text, $matches)) {
                    $data['price'] = floatval(str_replace(',', '', $matches[0]));
                    break;
                }
            }
        }

        // Try common selectors for description
        $desc_selectors = [
            '//div[contains(@class, "description")]',
            '//div[@itemprop="description"]',
            '//div[contains(@class, "product-description")]',
        ];

        foreach ($desc_selectors as $selector) {
            $desc = $xpath->query($selector);
            if ($desc->length > 0) {
                $data['description'] = trim($desc->item(0)->textContent);
                break;
            }
        }

        return $data;
    }

    /**
     * Process scraped data from N8N
     */
    private function process_scraped_data($data) {
        $processed = [
            'title' => sanitize_text_field($data['title'] ?? ''),
            'description' => wp_kses_post($data['description'] ?? ''),
            'short_description' => wp_kses_post($data['short_description'] ?? ''),
            'price' => floatval($data['price'] ?? 0),
            'images' => array_map('esc_url', $data['images'] ?? []),
            'category_id' => intval($data['category_id'] ?? 0),
            'source_url' => esc_url($data['source_url'] ?? ''),
            'specifications' => $data['specifications'] ?? [],
            'sku' => sanitize_text_field($data['sku'] ?? ''),
        ];

        // Apply price markup if not already applied
        if (!empty($data['apply_markup']) && $data['apply_markup']) {
            $markup = get_option('neogen_importer_price_markup', 30);
            $processed['price'] = $processed['price'] * (1 + $markup / 100);
        }

        return $processed;
    }

    /**
     * Create WooCommerce product
     */
    private function create_wc_product($data) {
        if (!class_exists('WC_Product')) {
            return new WP_Error('wc_not_active', 'WooCommerce غير مفعل');
        }

        $product = new WC_Product_Simple();

        $product->set_name($data['title']);
        $product->set_description($data['description'] ?? '');
        $product->set_short_description($data['short_description'] ?? '');

        if (!empty($data['price'])) {
            $product->set_regular_price($data['price']);
        }

        if (!empty($data['sku'])) {
            $product->set_sku($data['sku']);
        }

        // Set category
        if (!empty($data['category_id'])) {
            $product->set_category_ids([$data['category_id']]);
        } else {
            $default_cat = get_option('neogen_importer_default_category');
            if ($default_cat) {
                $product->set_category_ids([$default_cat]);
            }
        }

        // Set status
        $as_draft = get_option('neogen_importer_import_as_draft', 'yes');
        $product->set_status($as_draft === 'yes' ? 'draft' : 'publish');

        // Save product first to get ID
        $product_id = $product->save();

        // Handle images
        if (!empty($data['images'])) {
            $image_ids = [];

            foreach ($data['images'] as $index => $image_url) {
                $attachment_id = $this->upload_image_from_url($image_url, $product_id);

                if (!is_wp_error($attachment_id)) {
                    $image_ids[] = $attachment_id;

                    // First image is featured
                    if ($index === 0) {
                        $product->set_image_id($attachment_id);
                    }
                }
            }

            // Set gallery images (excluding featured)
            if (count($image_ids) > 1) {
                $product->set_gallery_image_ids(array_slice($image_ids, 1));
            }

            $product->save();
        }

        // Store source URL as meta
        if (!empty($data['source_url'])) {
            update_post_meta($product_id, '_neogen_source_url', $data['source_url']);
        }

        // Store specifications as meta
        if (!empty($data['specifications'])) {
            update_post_meta($product_id, '_neogen_specifications', $data['specifications']);
        }

        return $product_id;
    }

    /**
     * Upload image from URL
     */
    private function upload_image_from_url($url, $product_id = 0) {
        // Validate URL scheme - only allow http/https to prevent SSRF
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return new WP_Error('invalid_url_scheme', 'Only http and https URLs are allowed');
        }

        // Block internal/private IP ranges
        $host = parse_url($url, PHP_URL_HOST);
        if ($host) {
            $ip = gethostbyname($host);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return new WP_Error('blocked_url', 'URLs pointing to internal networks are not allowed');
            }
        }

        // Require necessary files
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Download the file
        $tmp = download_url($url);

        if (is_wp_error($tmp)) {
            return $tmp;
        }

        // Get file info
        $file_array = [
            'name' => basename(parse_url($url, PHP_URL_PATH)),
            'tmp_name' => $tmp,
        ];

        // If no extension, add .jpg
        if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file_array['name'])) {
            $file_array['name'] .= '.jpg';
        }

        // Upload to media library
        $attachment_id = media_handle_sideload($file_array, $product_id);

        // Clean up temp file
        if (file_exists($tmp)) {
            unlink($tmp);
        }

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        return $attachment_id;
    }

    /**
     * Get recent imports
     */
    public static function get_recent_imports($limit = 20, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_imports';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit, $offset
        ));
    }

    /**
     * Get import stats
     */
    public static function get_import_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_imports';

        return [
            'total' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
            'completed' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'completed'"),
            'failed' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'failed'"),
            'pending' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status IN ('pending', 'processing')"),
        ];
    }
}

// Initialize
function neogen_product_importer() {
    return Neogen_Product_Importer::instance();
}

add_action('plugins_loaded', 'neogen_product_importer');
