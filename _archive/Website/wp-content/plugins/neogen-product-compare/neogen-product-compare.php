<?php
/**
 * Plugin Name: Neogen Product Compare
 * Description: Unifi-style product comparison tool for WooCommerce
 * Version: 1.0.0
 * Author: neogen
 * Text Domain: neogen-compare
 */

defined('ABSPATH') || exit;

class Neogen_Product_Compare {

    private static $instance = null;
    private $max_products = 4;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Frontend
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'render_compare_bar']);

        // Add compare button to products
        add_action('woocommerce_after_shop_loop_item', [$this, 'add_compare_button_loop'], 15);
        add_action('woocommerce_single_product_summary', [$this, 'add_compare_button_single'], 35);

        // AJAX handlers
        add_action('wp_ajax_neogen_add_to_compare', [$this, 'ajax_add_to_compare']);
        add_action('wp_ajax_nopriv_neogen_add_to_compare', [$this, 'ajax_add_to_compare']);
        add_action('wp_ajax_neogen_remove_from_compare', [$this, 'ajax_remove_from_compare']);
        add_action('wp_ajax_nopriv_neogen_remove_from_compare', [$this, 'ajax_remove_from_compare']);
        add_action('wp_ajax_neogen_get_compare_data', [$this, 'ajax_get_compare_data']);
        add_action('wp_ajax_nopriv_neogen_get_compare_data', [$this, 'ajax_get_compare_data']);
        add_action('wp_ajax_neogen_clear_compare', [$this, 'ajax_clear_compare']);
        add_action('wp_ajax_nopriv_neogen_clear_compare', [$this, 'ajax_clear_compare']);

        // Compare page
        add_shortcode('neogen_compare', [$this, 'compare_page_shortcode']);

        // Register compare page on activation
        register_activation_hook(__FILE__, [$this, 'create_compare_page']);
    }

    /**
     * Create compare page on activation
     */
    public function create_compare_page() {
        $page = get_page_by_path('compare');
        if (!$page) {
            wp_insert_post([
                'post_title' => __('مقارنة المنتجات', 'neogen-compare'),
                'post_name' => 'compare',
                'post_content' => '[neogen_compare]',
                'post_status' => 'publish',
                'post_type' => 'page',
            ]);
        }
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'neogen-compare',
            plugin_dir_url(__FILE__) . 'assets/style.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'neogen-compare',
            plugin_dir_url(__FILE__) . 'assets/script.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('neogen-compare', 'neogenCompare', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('neogen_compare_nonce'),
            'compare_url' => get_permalink(get_page_by_path('compare')),
            'max_products' => $this->max_products,
            'strings' => [
                'add' => __('أضف للمقارنة', 'neogen-compare'),
                'remove' => __('إزالة من المقارنة', 'neogen-compare'),
                'added' => __('تمت الإضافة', 'neogen-compare'),
                'compare_now' => __('قارن الآن', 'neogen-compare'),
                'clear_all' => __('مسح الكل', 'neogen-compare'),
                'max_reached' => __('الحد الأقصى 4 منتجات', 'neogen-compare'),
                'add_more' => __('أضف منتج آخر', 'neogen-compare'),
            ]
        ]);
    }

    /**
     * Add compare button in shop loop
     */
    public function add_compare_button_loop() {
        global $product;
        $this->render_compare_button($product->get_id(), 'loop');
    }

    /**
     * Add compare button on single product
     */
    public function add_compare_button_single() {
        global $product;
        $this->render_compare_button($product->get_id(), 'single');
    }

    /**
     * Render compare button
     */
    private function render_compare_button($product_id, $context = 'loop') {
        ?>
        <button type="button"
                class="neogen-compare-btn <?php echo esc_attr($context); ?>"
                data-product-id="<?php echo esc_attr($product_id); ?>"
                title="<?php esc_attr_e('أضف للمقارنة', 'neogen-compare'); ?>">
            <svg class="compare-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 5H2v14h7M15 5h7v14h-7M9 12h6"/>
            </svg>
            <span class="compare-text"><?php _e('قارن', 'neogen-compare'); ?></span>
        </button>
        <?php
    }

    /**
     * Render floating compare bar
     */
    public function render_compare_bar() {
        $compare_page = get_page_by_path('compare');
        $compare_url = $compare_page ? get_permalink($compare_page) : '#';
        ?>
        <div id="neogen-compare-bar" class="neogen-compare-bar" style="display: none;">
            <div class="compare-bar-inner">
                <div class="compare-bar-products"></div>
                <div class="compare-bar-actions">
                    <span class="compare-count">
                        <span class="count-number">0</span> <?php _e('منتجات', 'neogen-compare'); ?>
                    </span>
                    <a href="<?php echo esc_url($compare_url); ?>" class="btn-compare-now">
                        <?php _e('قارن الآن', 'neogen-compare'); ?>
                    </a>
                    <button type="button" class="btn-clear-compare">
                        <?php _e('مسح', 'neogen-compare'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: Add to compare
     */
    public function ajax_add_to_compare() {
        check_ajax_referer('neogen_compare_nonce', 'nonce');

        $product_id = intval($_POST['product_id']);

        if (!$product_id) {
            wp_send_json_error(['message' => 'Invalid product']);
        }

        $compare_list = $this->get_compare_list();

        if (count($compare_list) >= $this->max_products) {
            wp_send_json_error([
                'message' => __('الحد الأقصى 4 منتجات للمقارنة', 'neogen-compare'),
                'max_reached' => true
            ]);
        }

        if (!in_array($product_id, $compare_list)) {
            $compare_list[] = $product_id;
            $this->save_compare_list($compare_list);
        }

        wp_send_json_success([
            'count' => count($compare_list),
            'products' => $this->get_compare_products_data($compare_list),
        ]);
    }

    /**
     * AJAX: Remove from compare
     */
    public function ajax_remove_from_compare() {
        check_ajax_referer('neogen_compare_nonce', 'nonce');

        $product_id = intval($_POST['product_id']);
        $compare_list = $this->get_compare_list();

        $compare_list = array_diff($compare_list, [$product_id]);
        $compare_list = array_values($compare_list);

        $this->save_compare_list($compare_list);

        wp_send_json_success([
            'count' => count($compare_list),
            'products' => $this->get_compare_products_data($compare_list),
        ]);
    }

    /**
     * AJAX: Get compare data
     */
    public function ajax_get_compare_data() {
        check_ajax_referer('neogen_compare_nonce', 'nonce');

        $compare_list = $this->get_compare_list();

        wp_send_json_success([
            'count' => count($compare_list),
            'products' => $this->get_compare_products_data($compare_list),
            'ids' => $compare_list,
        ]);
    }

    /**
     * AJAX: Clear compare
     */
    public function ajax_clear_compare() {
        check_ajax_referer('neogen_compare_nonce', 'nonce');

        $this->save_compare_list([]);

        wp_send_json_success(['count' => 0, 'products' => []]);
    }

    /**
     * Get compare list from cookie/session
     */
    private function get_compare_list() {
        if (isset($_COOKIE['neogen_compare'])) {
            $list = json_decode(stripslashes($_COOKIE['neogen_compare']), true);
            if (is_array($list)) {
                return array_map('intval', $list);
            }
        }
        return [];
    }

    /**
     * Save compare list to cookie
     */
    private function save_compare_list($list) {
        setcookie('neogen_compare', json_encode($list), time() + (86400 * 7), '/');
        $_COOKIE['neogen_compare'] = json_encode($list);
    }

    /**
     * Get products data for compare bar
     */
    private function get_compare_products_data($product_ids) {
        $products = [];

        foreach ($product_ids as $id) {
            $product = wc_get_product($id);
            if (!$product) continue;

            $products[] = [
                'id' => $id,
                'name' => $product->get_name(),
                'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
                'price' => $product->get_price_html(),
                'url' => get_permalink($id),
            ];
        }

        return $products;
    }

    /**
     * Compare page shortcode
     */
    public function compare_page_shortcode() {
        $compare_list = $this->get_compare_list();

        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/compare-page.php';
        return ob_get_clean();
    }

    /**
     * Get product attributes for comparison
     */
    public static function get_comparison_attributes($product_ids) {
        $all_attributes = [];
        $products_data = [];

        foreach ($product_ids as $id) {
            $product = wc_get_product($id);
            if (!$product) continue;

            $product_data = [
                'id' => $id,
                'name' => $product->get_name(),
                'image' => wp_get_attachment_image_url($product->get_image_id(), 'medium'),
                'price' => $product->get_price(),
                'price_html' => $product->get_price_html(),
                'url' => get_permalink($id),
                'add_to_cart_url' => $product->add_to_cart_url(),
                'in_stock' => $product->is_in_stock(),
                'rating' => $product->get_average_rating(),
                'review_count' => $product->get_review_count(),
                'short_description' => $product->get_short_description(),
                'attributes' => [],
            ];

            // Get product attributes
            $attributes = $product->get_attributes();
            foreach ($attributes as $attr) {
                if ($attr->is_taxonomy()) {
                    $name = wc_attribute_label($attr->get_name());
                    $values = wc_get_product_terms($id, $attr->get_name(), ['fields' => 'names']);
                    $value = implode(', ', $values);
                } else {
                    $name = $attr->get_name();
                    $value = implode(', ', $attr->get_options());
                }

                $product_data['attributes'][$name] = $value;
                $all_attributes[$name] = true;
            }

            // Add custom meta fields
            $custom_fields = [
                '_neogen_hub_required' => __('يتطلب Hub', 'neogen-compare'),
                '_neogen_connectivity' => __('الاتصال', 'neogen-compare'),
                '_neogen_battery_life' => __('عمر البطارية', 'neogen-compare'),
                '_neogen_warranty' => __('الضمان', 'neogen-compare'),
            ];

            foreach ($custom_fields as $key => $label) {
                $value = get_post_meta($id, $key, true);
                if ($value) {
                    $product_data['attributes'][$label] = $value;
                    $all_attributes[$label] = true;
                }
            }

            $products_data[] = $product_data;
        }

        return [
            'products' => $products_data,
            'attributes' => array_keys($all_attributes),
        ];
    }
}

// Initialize
Neogen_Product_Compare::instance();
