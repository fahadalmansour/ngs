<?php
/**
 * Plugin Name: Neogen Product Registration
 * Description: Serial number registration, warranty tracking, and PDF manual access for neogen customers
 * Version: 1.0.0
 * Author: neogen
 * Text Domain: neogen-registration
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

defined('ABSPATH') || exit;

// Plugin constants
define('NEOGEN_REG_VERSION', '1.0.0');
define('NEOGEN_REG_PATH', plugin_dir_path(__FILE__));
define('NEOGEN_REG_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Neogen_Product_Registration {

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

        // Admin
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('add_meta_boxes', [$this, 'add_order_meta_box']);
        add_action('woocommerce_process_shop_order_meta', [$this, 'save_order_serial_numbers']);

        // Product meta box for PDF uploads
        add_action('add_meta_boxes', [$this, 'add_product_meta_box']);
        add_action('woocommerce_process_product_meta', [$this, 'save_product_manual']);

        // My Account
        add_action('init', [$this, 'add_endpoints']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_account_menu_item']);
        add_action('woocommerce_account_my-products_endpoint', [$this, 'my_products_content']);

        // AJAX handlers
        add_action('wp_ajax_neogen_register_serial', [$this, 'ajax_register_serial']);
        add_action('wp_ajax_neogen_download_manual', [$this, 'ajax_download_manual']);

        // Enqueue scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }

    /**
     * Plugin activation - create database table
     */
    public function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'neogen_serials';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            serial_number varchar(100) NOT NULL,
            product_id bigint(20) NOT NULL,
            order_id bigint(20) DEFAULT NULL,
            customer_id bigint(20) DEFAULT NULL,
            registered_by varchar(20) DEFAULT 'customer',
            registration_date datetime DEFAULT CURRENT_TIMESTAMP,
            warranty_start date DEFAULT NULL,
            warranty_end date DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            notes text,
            PRIMARY KEY (id),
            UNIQUE KEY serial_number (serial_number),
            KEY product_id (product_id),
            KEY customer_id (customer_id),
            KEY order_id (order_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Flush rewrite rules for new endpoint
        flush_rewrite_rules();
    }

    /**
     * Add My Account endpoint
     */
    public function add_endpoints() {
        add_rewrite_endpoint('my-products', EP_ROOT | EP_PAGES);
    }

    /**
     * Add menu item to My Account
     */
    public function add_account_menu_item($items) {
        $new_items = [];
        foreach ($items as $key => $value) {
            $new_items[$key] = $value;
            if ($key === 'orders') {
                $new_items['my-products'] = __('منتجاتي المسجلة', 'neogen-registration');
            }
        }
        return $new_items;
    }

    /**
     * My Products page content
     */
    public function my_products_content() {
        $customer_id = get_current_user_id();

        if (!$customer_id) {
            echo '<p>' . __('يرجى تسجيل الدخول للوصول إلى منتجاتك.', 'neogen-registration') . '</p>';
            return;
        }

        // Get customer's orders
        $orders = wc_get_orders([
            'customer_id' => $customer_id,
            'status' => ['completed', 'processing'],
            'limit' => -1,
        ]);

        // Get registered serials
        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_serials';
        $registered_serials = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE customer_id = %d ORDER BY registration_date DESC",
            $customer_id
        ));

        include NEOGEN_REG_PATH . 'templates/my-products.php';
    }

    /**
     * Admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Serial Numbers', 'neogen-registration'),
            __('Serial Numbers', 'neogen-registration'),
            'manage_woocommerce',
            'neogen-serials',
            [$this, 'admin_serials_page'],
            'dashicons-shield',
            56
        );
    }

    /**
     * Admin serials page
     */
    public function admin_serials_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_serials';

        // Handle form submission
        if (isset($_POST['neogen_add_serial']) && wp_verify_nonce($_POST['_wpnonce'], 'neogen_add_serial')) {
            $serial = sanitize_text_field($_POST['serial_number']);
            $product_id = intval($_POST['product_id']);
            $order_id = !empty($_POST['order_id']) ? intval($_POST['order_id']) : null;
            $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
            $warranty_months = intval($_POST['warranty_months']);

            $warranty_start = current_time('Y-m-d');
            $warranty_end = date('Y-m-d', strtotime("+$warranty_months months"));

            $wpdb->insert($table_name, [
                'serial_number' => $serial,
                'product_id' => $product_id,
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'registered_by' => 'admin',
                'warranty_start' => $warranty_start,
                'warranty_end' => $warranty_end,
                'status' => 'active',
            ]);

            echo '<div class="notice notice-success"><p>تم إضافة الرقم التسلسلي بنجاح!</p></div>';
        }

        // Get all serials
        $serials = $wpdb->get_results("SELECT * FROM $table_name ORDER BY registration_date DESC LIMIT 100");

        include NEOGEN_REG_PATH . 'templates/admin-serials.php';
    }

    /**
     * Order meta box for serial numbers
     */
    public function add_order_meta_box() {
        add_meta_box(
            'neogen_order_serials',
            __('Serial Numbers', 'neogen-registration'),
            [$this, 'order_meta_box_content'],
            'shop_order',
            'side',
            'high'
        );

        // HPOS compatibility
        add_meta_box(
            'neogen_order_serials',
            __('Serial Numbers', 'neogen-registration'),
            [$this, 'order_meta_box_content'],
            'woocommerce_page_wc-orders',
            'side',
            'high'
        );
    }

    /**
     * Order meta box content
     */
    public function order_meta_box_content($post_or_order) {
        $order = ($post_or_order instanceof WP_Post) ? wc_get_order($post_or_order->ID) : $post_or_order;

        if (!$order) return;

        $order_id = $order->get_id();

        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_serials';

        wp_nonce_field('neogen_order_serials', 'neogen_serials_nonce');

        echo '<div class="neogen-order-serials">';

        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $product_name = $item->get_name();
            $qty = $item->get_quantity();

            // Get existing serials for this item
            $existing_serials = $wpdb->get_results($wpdb->prepare(
                "SELECT serial_number FROM $table_name WHERE order_id = %d AND product_id = %d",
                $order_id, $product_id
            ));

            echo '<div class="serial-item" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px;">';
            echo '<strong style="display: block; margin-bottom: 8px;">' . esc_html($product_name) . ' (x' . $qty . ')</strong>';

            for ($i = 0; $i < $qty; $i++) {
                $existing = isset($existing_serials[$i]) ? $existing_serials[$i]->serial_number : '';
                echo '<input type="text" name="neogen_serial[' . $item_id . '][' . $i . ']" value="' . esc_attr($existing) . '" placeholder="Serial #' . ($i + 1) . '" style="width: 100%; margin-bottom: 5px;" />';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Save order serial numbers
     */
    public function save_order_serial_numbers($order_id) {
        if (!isset($_POST['neogen_serials_nonce']) || !wp_verify_nonce($_POST['neogen_serials_nonce'], 'neogen_order_serials')) {
            return;
        }

        if (!isset($_POST['neogen_serial'])) return;

        $order = wc_get_order($order_id);
        if (!$order) return;

        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_serials';

        foreach ($_POST['neogen_serial'] as $item_id => $serials) {
            $item = $order->get_item($item_id);
            if (!$item) continue;

            $product_id = $item->get_product_id();
            $customer_id = $order->get_customer_id();

            foreach ($serials as $serial) {
                $serial = sanitize_text_field($serial);
                if (empty($serial)) continue;

                // Check if serial exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_name WHERE serial_number = %s",
                    $serial
                ));

                if ($exists) {
                    // Update existing
                    $wpdb->update($table_name, [
                        'order_id' => $order_id,
                        'customer_id' => $customer_id,
                        'registered_by' => 'admin',
                        'warranty_start' => current_time('Y-m-d'),
                        'warranty_end' => date('Y-m-d', strtotime('+12 months')),
                    ], ['serial_number' => $serial]);
                } else {
                    // Insert new
                    $wpdb->insert($table_name, [
                        'serial_number' => $serial,
                        'product_id' => $product_id,
                        'order_id' => $order_id,
                        'customer_id' => $customer_id,
                        'registered_by' => 'admin',
                        'warranty_start' => current_time('Y-m-d'),
                        'warranty_end' => date('Y-m-d', strtotime('+12 months')),
                        'status' => 'active',
                    ]);
                }
            }
        }
    }

    /**
     * Product meta box for PDF manual
     */
    public function add_product_meta_box() {
        add_meta_box(
            'neogen_product_manual',
            __('Product Manual (PDF)', 'neogen-registration'),
            [$this, 'product_meta_box_content'],
            'product',
            'side',
            'default'
        );
    }

    /**
     * Product meta box content
     */
    public function product_meta_box_content($post) {
        $manual_id = get_post_meta($post->ID, '_neogen_manual_id', true);
        $manual_url = $manual_id ? wp_get_attachment_url($manual_id) : '';

        wp_nonce_field('neogen_product_manual', 'neogen_manual_nonce');

        echo '<div class="neogen-manual-upload">';
        echo '<input type="hidden" name="neogen_manual_id" id="neogen_manual_id" value="' . esc_attr($manual_id) . '" />';

        if ($manual_url) {
            echo '<p><a href="' . esc_url($manual_url) . '" target="_blank">📄 ' . basename($manual_url) . '</a></p>';
            echo '<button type="button" class="button" id="neogen_remove_manual">حذف</button> ';
        }

        echo '<button type="button" class="button button-primary" id="neogen_upload_manual">' . ($manual_url ? 'تغيير' : 'رفع PDF') . '</button>';
        echo '</div>';

        echo '<script>
        jQuery(document).ready(function($) {
            var frame;
            $("#neogen_upload_manual").on("click", function(e) {
                e.preventDefault();
                if (frame) { frame.open(); return; }
                frame = wp.media({
                    title: "اختر ملف PDF",
                    button: { text: "استخدام هذا الملف" },
                    library: { type: "application/pdf" },
                    multiple: false
                });
                frame.on("select", function() {
                    var attachment = frame.state().get("selection").first().toJSON();
                    $("#neogen_manual_id").val(attachment.id);
                    location.reload();
                });
                frame.open();
            });
            $("#neogen_remove_manual").on("click", function(e) {
                e.preventDefault();
                $("#neogen_manual_id").val("");
                $(this).parent().find("p").remove();
                $(this).remove();
            });
        });
        </script>';
    }

    /**
     * Save product manual
     */
    public function save_product_manual($post_id) {
        if (!isset($_POST['neogen_manual_nonce']) || !wp_verify_nonce($_POST['neogen_manual_nonce'], 'neogen_product_manual')) {
            return;
        }

        if (isset($_POST['neogen_manual_id'])) {
            update_post_meta($post_id, '_neogen_manual_id', sanitize_text_field($_POST['neogen_manual_id']));
        }
    }

    /**
     * AJAX: Register serial number (customer)
     */
    public function ajax_register_serial() {
        check_ajax_referer('neogen_register_serial', 'nonce');

        $customer_id = get_current_user_id();
        if (!$customer_id) {
            wp_send_json_error(['message' => 'يرجى تسجيل الدخول أولاً']);
        }

        $serial = sanitize_text_field($_POST['serial_number']);
        $order_id = intval($_POST['order_id']);
        $product_id = intval($_POST['product_id']);

        if (empty($serial)) {
            wp_send_json_error(['message' => 'يرجى إدخال الرقم التسلسلي']);
        }

        // Verify order belongs to customer
        $order = wc_get_order($order_id);
        if (!$order || $order->get_customer_id() != $customer_id) {
            wp_send_json_error(['message' => 'طلب غير صالح']);
        }

        // Verify product is in order
        $found = false;
        foreach ($order->get_items() as $item) {
            if ($item->get_product_id() == $product_id) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            wp_send_json_error(['message' => 'المنتج غير موجود في هذا الطلب']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_serials';

        // Check if serial already registered
        $exists = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE serial_number = %s",
            $serial
        ));

        if ($exists && $exists->customer_id && $exists->customer_id != $customer_id) {
            wp_send_json_error(['message' => 'هذا الرقم التسلسلي مسجل بالفعل لعميل آخر']);
        }

        if ($exists) {
            // Update existing
            $wpdb->update($table_name, [
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'warranty_start' => current_time('Y-m-d'),
                'warranty_end' => date('Y-m-d', strtotime('+12 months')),
            ], ['serial_number' => $serial]);
        } else {
            // Insert new
            $wpdb->insert($table_name, [
                'serial_number' => $serial,
                'product_id' => $product_id,
                'order_id' => $order_id,
                'customer_id' => $customer_id,
                'registered_by' => 'customer',
                'warranty_start' => current_time('Y-m-d'),
                'warranty_end' => date('Y-m-d', strtotime('+12 months')),
                'status' => 'active',
            ]);
        }

        wp_send_json_success(['message' => 'تم تسجيل المنتج بنجاح! الآن يمكنك تحميل الدليل.']);
    }

    /**
     * AJAX: Download manual (requires registered serial)
     */
    public function ajax_download_manual() {
        check_ajax_referer('neogen_download_manual', 'nonce');

        $customer_id = get_current_user_id();
        if (!$customer_id) {
            wp_send_json_error(['message' => 'يرجى تسجيل الدخول أولاً']);
        }

        $product_id = intval($_POST['product_id']);

        // Check if customer has registered serial for this product
        global $wpdb;
        $table_name = $wpdb->prefix . 'neogen_serials';

        $registered = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE customer_id = %d AND product_id = %d",
            $customer_id, $product_id
        ));

        if (!$registered) {
            wp_send_json_error(['message' => 'يجب تسجيل الرقم التسلسلي للمنتج أولاً للوصول إلى الدليل']);
        }

        // Get manual URL
        $manual_id = get_post_meta($product_id, '_neogen_manual_id', true);
        if (!$manual_id) {
            wp_send_json_error(['message' => 'لا يوجد دليل متاح لهذا المنتج']);
        }

        $manual_url = wp_get_attachment_url($manual_id);

        wp_send_json_success(['url' => $manual_url]);
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        if (is_account_page()) {
            wp_enqueue_style('neogen-registration', NEOGEN_REG_URL . 'assets/style.css', [], NEOGEN_REG_VERSION);
            wp_enqueue_script('neogen-registration', NEOGEN_REG_URL . 'assets/script.js', ['jquery'], NEOGEN_REG_VERSION, true);
            wp_localize_script('neogen-registration', 'neogenReg', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'register_nonce' => wp_create_nonce('neogen_register_serial'),
                'download_nonce' => wp_create_nonce('neogen_download_manual'),
            ]);
        }
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if (in_array($hook, ['post.php', 'post-new.php'])) {
            wp_enqueue_media();
        }
    }
}

// Initialize
function neogen_product_registration() {
    return Neogen_Product_Registration::instance();
}

add_action('plugins_loaded', 'neogen_product_registration');
