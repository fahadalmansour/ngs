<?php
/**
 * Plugin Name: Neogen WhatsApp Integration
 * Description: Floating WhatsApp button with pre-filled messages for product inquiries and order tracking
 * Version: 1.0.0
 * Author: neogen
 * Text Domain: neogen-whatsapp
 */

defined('ABSPATH') || exit;

class Neogen_WhatsApp {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Settings
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Frontend
        add_action('wp_footer', [$this, 'render_whatsapp_button']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Product page button
        add_action('woocommerce_single_product_summary', [$this, 'product_inquiry_button'], 35);

        // Order tracking
        add_action('woocommerce_order_details_after_order_table', [$this, 'order_whatsapp_button']);

        // Cart page
        add_action('woocommerce_after_cart_totals', [$this, 'cart_whatsapp_button']);

        // Shortcode
        add_shortcode('neogen_whatsapp', [$this, 'whatsapp_shortcode']);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_number');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_message');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_product_message');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_order_message');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_cart_message');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_position');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_show_on_mobile');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_show_on_desktop');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_button_text');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_tooltip');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_cta_enabled');
        register_setting('neogen_whatsapp_settings', 'neogen_whatsapp_cta_text');

        // Set defaults
        add_option('neogen_whatsapp_message', 'مرحباً، أريد الاستفسار عن منتجاتكم');
        add_option('neogen_whatsapp_product_message', 'مرحباً، أريد الاستفسار عن: {product_name} - {product_url}');
        add_option('neogen_whatsapp_order_message', 'مرحباً، أريد متابعة طلبي رقم: {order_id}');
        add_option('neogen_whatsapp_cart_message', 'مرحباً، لدي استفسار عن طلبي');
        add_option('neogen_whatsapp_position', 'bottom-left');
        add_option('neogen_whatsapp_show_on_mobile', 'yes');
        add_option('neogen_whatsapp_show_on_desktop', 'yes');
        add_option('neogen_whatsapp_button_text', '');
        add_option('neogen_whatsapp_tooltip', 'تواصل معنا عبر واتساب');
        add_option('neogen_whatsapp_cta_enabled', 'yes');
        add_option('neogen_whatsapp_cta_text', 'هل تحتاج مساعدة؟');
    }

    /**
     * Admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('WhatsApp Settings', 'neogen-whatsapp'),
            __('WhatsApp', 'neogen-whatsapp'),
            'manage_options',
            'neogen-whatsapp',
            [$this, 'settings_page']
        );
    }

    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('إعدادات واتساب', 'neogen-whatsapp'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('neogen_whatsapp_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('رقم الواتساب', 'neogen-whatsapp'); ?></th>
                        <td>
                            <input type="text" name="neogen_whatsapp_number"
                                value="<?php echo esc_attr(get_option('neogen_whatsapp_number')); ?>"
                                class="regular-text" placeholder="966501234567" dir="ltr" />
                            <p class="description"><?php _e('أدخل الرقم بالصيغة الدولية بدون + أو 00', 'neogen-whatsapp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('الرسالة الافتراضية', 'neogen-whatsapp'); ?></th>
                        <td>
                            <textarea name="neogen_whatsapp_message" rows="3" class="large-text"><?php echo esc_textarea(get_option('neogen_whatsapp_message')); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('رسالة صفحة المنتج', 'neogen-whatsapp'); ?></th>
                        <td>
                            <textarea name="neogen_whatsapp_product_message" rows="3" class="large-text"><?php echo esc_textarea(get_option('neogen_whatsapp_product_message')); ?></textarea>
                            <p class="description"><?php _e('المتغيرات: {product_name}, {product_url}, {product_price}', 'neogen-whatsapp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('رسالة تتبع الطلب', 'neogen-whatsapp'); ?></th>
                        <td>
                            <textarea name="neogen_whatsapp_order_message" rows="3" class="large-text"><?php echo esc_textarea(get_option('neogen_whatsapp_order_message')); ?></textarea>
                            <p class="description"><?php _e('المتغيرات: {order_id}, {order_total}', 'neogen-whatsapp'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('رسالة صفحة السلة', 'neogen-whatsapp'); ?></th>
                        <td>
                            <textarea name="neogen_whatsapp_cart_message" rows="3" class="large-text"><?php echo esc_textarea(get_option('neogen_whatsapp_cart_message')); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('موقع الزر', 'neogen-whatsapp'); ?></th>
                        <td>
                            <select name="neogen_whatsapp_position">
                                <option value="bottom-left" <?php selected(get_option('neogen_whatsapp_position'), 'bottom-left'); ?>><?php _e('أسفل يسار', 'neogen-whatsapp'); ?></option>
                                <option value="bottom-right" <?php selected(get_option('neogen_whatsapp_position'), 'bottom-right'); ?>><?php _e('أسفل يمين', 'neogen-whatsapp'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('إظهار على', 'neogen-whatsapp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="neogen_whatsapp_show_on_desktop" value="yes"
                                    <?php checked(get_option('neogen_whatsapp_show_on_desktop'), 'yes'); ?> />
                                <?php _e('سطح المكتب', 'neogen-whatsapp'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="neogen_whatsapp_show_on_mobile" value="yes"
                                    <?php checked(get_option('neogen_whatsapp_show_on_mobile'), 'yes'); ?> />
                                <?php _e('الجوال', 'neogen-whatsapp'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('نص الزر (اختياري)', 'neogen-whatsapp'); ?></th>
                        <td>
                            <input type="text" name="neogen_whatsapp_button_text"
                                value="<?php echo esc_attr(get_option('neogen_whatsapp_button_text')); ?>"
                                class="regular-text" placeholder="تواصل معنا" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('التلميح', 'neogen-whatsapp'); ?></th>
                        <td>
                            <input type="text" name="neogen_whatsapp_tooltip"
                                value="<?php echo esc_attr(get_option('neogen_whatsapp_tooltip')); ?>"
                                class="regular-text" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('فقاعة CTA', 'neogen-whatsapp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="neogen_whatsapp_cta_enabled" value="yes"
                                    <?php checked(get_option('neogen_whatsapp_cta_enabled'), 'yes'); ?> />
                                <?php _e('إظهار فقاعة تنبيه', 'neogen-whatsapp'); ?>
                            </label>
                            <br><br>
                            <input type="text" name="neogen_whatsapp_cta_text"
                                value="<?php echo esc_attr(get_option('neogen_whatsapp_cta_text')); ?>"
                                class="regular-text" />
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>

            <h2><?php _e('الاختصارات', 'neogen-whatsapp'); ?></h2>
            <p><code>[neogen_whatsapp text="تواصل معنا" message="رسالة مخصصة"]</code></p>
        </div>
        <?php
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        if (!$this->should_show()) {
            return;
        }

        wp_enqueue_style(
            'neogen-whatsapp',
            plugin_dir_url(__FILE__) . 'assets/style.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'neogen-whatsapp',
            plugin_dir_url(__FILE__) . 'assets/script.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    /**
     * Should show button
     */
    private function should_show() {
        $number = get_option('neogen_whatsapp_number');
        if (empty($number)) {
            return false;
        }

        $show_desktop = get_option('neogen_whatsapp_show_on_desktop') === 'yes';
        $show_mobile = get_option('neogen_whatsapp_show_on_mobile') === 'yes';

        if (!$show_desktop && !$show_mobile) {
            return false;
        }

        return true;
    }

    /**
     * Render floating WhatsApp button
     */
    public function render_whatsapp_button() {
        if (!$this->should_show()) {
            return;
        }

        $number = get_option('neogen_whatsapp_number');
        $message = get_option('neogen_whatsapp_message');
        $position = get_option('neogen_whatsapp_position', 'bottom-left');
        $button_text = get_option('neogen_whatsapp_button_text');
        $tooltip = get_option('neogen_whatsapp_tooltip');
        $cta_enabled = get_option('neogen_whatsapp_cta_enabled') === 'yes';
        $cta_text = get_option('neogen_whatsapp_cta_text');
        $show_desktop = get_option('neogen_whatsapp_show_on_desktop') === 'yes';
        $show_mobile = get_option('neogen_whatsapp_show_on_mobile') === 'yes';

        $url = $this->get_whatsapp_url($number, $message);

        $classes = ['neogen-whatsapp-float', 'position-' . $position];
        if (!$show_desktop) $classes[] = 'hide-desktop';
        if (!$show_mobile) $classes[] = 'hide-mobile';
        ?>

        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            <?php if ($cta_enabled && $cta_text): ?>
                <div class="neogen-wa-cta">
                    <span class="cta-text"><?php echo esc_html($cta_text); ?></span>
                    <button type="button" class="cta-close">&times;</button>
                </div>
            <?php endif; ?>

            <a href="<?php echo esc_url($url); ?>"
               class="neogen-wa-button"
               target="_blank"
               rel="noopener noreferrer"
               title="<?php echo esc_attr($tooltip); ?>">
                <svg viewBox="0 0 32 32" class="wa-icon">
                    <path fill="currentColor" d="M16.003 3.2c-7.067 0-12.8 5.733-12.8 12.8 0 2.267.6 4.467 1.733 6.4l-1.867 6.8 7.067-1.8c1.867 1 3.933 1.533 6.067 1.533h.067c7.067 0 12.8-5.733 12.8-12.8s-5.8-12.933-13.067-12.933zm0 23.467c-1.933 0-3.8-.533-5.467-1.467l-.4-.233-4.133 1.067 1.1-4-.267-.4c-1.067-1.733-1.6-3.667-1.6-5.633 0-5.867 4.8-10.667 10.733-10.667 2.867 0 5.533 1.133 7.567 3.133 2 2.033 3.133 4.7 3.133 7.567-.067 5.867-4.867 10.633-10.667 10.633zm5.867-8c-.333-.167-1.933-.933-2.233-1.067-.3-.1-.533-.167-.733.167-.2.333-.8 1.067-1 1.267-.167.2-.367.233-.7.067-.333-.167-1.4-.533-2.667-1.667-1-.867-1.667-1.967-1.867-2.3-.2-.333 0-.5.15-.667.133-.133.333-.333.467-.5.133-.167.2-.267.3-.467.1-.2.067-.367 0-.533-.067-.167-.733-1.767-1-2.433-.267-.633-.533-.533-.733-.533h-.633c-.2 0-.533.067-.8.4-.267.333-1.067 1.033-1.067 2.533s1.1 2.933 1.233 3.133c.167.2 2.133 3.267 5.167 4.567.733.3 1.3.5 1.733.633.733.233 1.4.2 1.933.133.6-.1 1.833-.767 2.1-1.5.233-.733.233-1.367.167-1.5-.067-.133-.267-.2-.6-.367z"/>
                </svg>
                <?php if ($button_text): ?>
                    <span class="wa-text"><?php echo esc_html($button_text); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <?php
    }

    /**
     * Product inquiry button
     */
    public function product_inquiry_button() {
        $number = get_option('neogen_whatsapp_number');
        if (empty($number)) {
            return;
        }

        global $product;
        if (!$product) {
            return;
        }

        $message = get_option('neogen_whatsapp_product_message');
        $message = str_replace(
            ['{product_name}', '{product_url}', '{product_price}'],
            [$product->get_name(), get_permalink($product->get_id()), $product->get_price()],
            $message
        );

        $url = $this->get_whatsapp_url($number, $message);
        ?>

        <div class="neogen-wa-product-btn">
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="button wa-product-button">
                <svg viewBox="0 0 32 32" class="wa-icon-small">
                    <path fill="currentColor" d="M16.003 3.2c-7.067 0-12.8 5.733-12.8 12.8 0 2.267.6 4.467 1.733 6.4l-1.867 6.8 7.067-1.8c1.867 1 3.933 1.533 6.067 1.533h.067c7.067 0 12.8-5.733 12.8-12.8s-5.8-12.933-13.067-12.933z"/>
                </svg>
                <?php _e('اسأل عن هذا المنتج', 'neogen-whatsapp'); ?>
            </a>
        </div>

        <?php
    }

    /**
     * Order WhatsApp button
     */
    public function order_whatsapp_button($order) {
        $number = get_option('neogen_whatsapp_number');
        if (empty($number)) {
            return;
        }

        $message = get_option('neogen_whatsapp_order_message');
        $message = str_replace(
            ['{order_id}', '{order_total}'],
            [$order->get_order_number(), $order->get_total()],
            $message
        );

        $url = $this->get_whatsapp_url($number, $message);
        ?>

        <div class="neogen-wa-order-btn">
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="button wa-order-button">
                <svg viewBox="0 0 32 32" class="wa-icon-small">
                    <path fill="currentColor" d="M16.003 3.2c-7.067 0-12.8 5.733-12.8 12.8 0 2.267.6 4.467 1.733 6.4l-1.867 6.8 7.067-1.8c1.867 1 3.933 1.533 6.067 1.533h.067c7.067 0 12.8-5.733 12.8-12.8s-5.8-12.933-13.067-12.933z"/>
                </svg>
                <?php _e('تواصل بخصوص هذا الطلب', 'neogen-whatsapp'); ?>
            </a>
        </div>

        <?php
    }

    /**
     * Cart WhatsApp button
     */
    public function cart_whatsapp_button() {
        $number = get_option('neogen_whatsapp_number');
        if (empty($number)) {
            return;
        }

        $message = get_option('neogen_whatsapp_cart_message');
        $url = $this->get_whatsapp_url($number, $message);
        ?>

        <div class="neogen-wa-cart-btn">
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="button wa-cart-button">
                <svg viewBox="0 0 32 32" class="wa-icon-small">
                    <path fill="currentColor" d="M16.003 3.2c-7.067 0-12.8 5.733-12.8 12.8 0 2.267.6 4.467 1.733 6.4l-1.867 6.8 7.067-1.8c1.867 1 3.933 1.533 6.067 1.533h.067c7.067 0 12.8-5.733 12.8-12.8s-5.8-12.933-13.067-12.933z"/>
                </svg>
                <?php _e('هل تحتاج مساعدة؟', 'neogen-whatsapp'); ?>
            </a>
        </div>

        <?php
    }

    /**
     * Shortcode
     */
    public function whatsapp_shortcode($atts) {
        $number = get_option('neogen_whatsapp_number');
        if (empty($number)) {
            return '';
        }

        $atts = shortcode_atts([
            'text' => __('تواصل عبر واتساب', 'neogen-whatsapp'),
            'message' => get_option('neogen_whatsapp_message'),
            'class' => '',
        ], $atts);

        $url = $this->get_whatsapp_url($number, $atts['message']);

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="neogen-wa-shortcode %s">%s</a>',
            esc_url($url),
            esc_attr($atts['class']),
            esc_html($atts['text'])
        );
    }

    /**
     * Get WhatsApp URL
     */
    private function get_whatsapp_url($number, $message = '') {
        $number = preg_replace('/[^0-9]/', '', $number);
        $base_url = 'https://wa.me/' . $number;

        if ($message) {
            $base_url .= '?text=' . rawurlencode($message);
        }

        return $base_url;
    }
}

// Initialize
Neogen_WhatsApp::instance();
