<?php
/**
 * Plugin Name: Neogen Theme Customizer
 * Description: Complete store layout system for Neogen smart home store - colors, typography, mobile nav, promo bar, mega menu, shop filters, product badges
 * Version: 2.0.0
 * Author: Neogen
 * Text Domain: neogen-theme
 */

defined('ABSPATH') || exit;

class Neogen_Theme_Customizer {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_fonts'], 5);
        add_action('wp_head', [$this, 'enqueue_styles'], 999);
        add_action('wp_footer', [$this, 'render_components'], 20);
        add_action('wp_footer', [$this, 'enqueue_scripts'], 999);
        add_action('wp_head', [$this, 'add_preload_theme']);
    }

    public function add_preload_theme() {
        ?>
        <script>
        (function() {
            var theme = localStorage.getItem('neogen-theme') || 'light';
            var lang = localStorage.getItem('neogen-lang') || 'ar';
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.setAttribute('lang', lang);
            document.documentElement.setAttribute('dir', lang === 'en' ? 'ltr' : 'rtl');
        })();
        </script>
        <meta name="theme-color" content="#1A3A5C">
        <?php
    }

    public function enqueue_fonts() {
        wp_enqueue_style(
            'neogen-google-fonts',
            'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap',
            [],
            null
        );
    }

    public function enqueue_styles() {
        echo '<style id="neogen-theme-css">' . $this->get_css() . '</style>';
    }

    public function render_components() {
        echo $this->get_promo_bar_html();
        echo $this->get_mega_menu_html();
        echo $this->get_mobile_nav_html();
    }

    public function enqueue_scripts() {
        ?>
        <script>
        <?php echo $this->get_js(); ?>
        </script>
        <?php
    }

    // =========================================================
    // PROMO BAR HTML
    // =========================================================
    private function get_promo_bar_html() {
        return '
        <div id="neogen-promo-bar" style="display:none;">
            <div class="neogen-promo-inner">
                <span class="neogen-promo-text">🔥 شحن مجاني على جميع الطلبات فوق 300 ر.س | خصم 10% للطلب الأول - كود: <strong>NEOGEN10</strong></span>
                <button class="neogen-promo-close" aria-label="إغلاق">&times;</button>
            </div>
        </div>';
    }

    // =========================================================
    // MEGA MENU HTML
    // =========================================================
    private function get_mega_menu_html() {
        return '
        <div id="neogen-mega-menu" class="neogen-mega-menu" style="display:none;">
            <div class="neogen-mega-inner">
                <div class="neogen-mega-col">
                    <h4><a href="/product-category/security-cameras/">الأمان والكاميرات</a></h4>
                    <ul>
                        <li><a href="/product-category/security-cameras/indoor-cameras/">كاميرات داخلية</a></li>
                        <li><a href="/product-category/security-cameras/outdoor-cameras/">كاميرات خارجية</a></li>
                        <li><a href="/product-category/security-cameras/smart-locks/">أقفال ذكية</a></li>
                        <li><a href="/product-category/security-cameras/video-doorbells/">أجراس ذكية</a></li>
                    </ul>
                </div>
                <div class="neogen-mega-col">
                    <h4><a href="/product-category/smart-lighting/">الإضاءة الذكية</a></h4>
                    <ul>
                        <li><a href="/product-category/smart-lighting/smart-bulbs/">لمبات ذكية</a></li>
                        <li><a href="/product-category/smart-lighting/led-strips/">شرائط LED</a></li>
                        <li><a href="/product-category/smart-lighting/light-panels/">لوحات إضاءة</a></li>
                    </ul>
                </div>
                <div class="neogen-mega-col">
                    <h4><a href="/product-category/sensors-hubs/">المستشعرات والمراكز</a></h4>
                    <ul>
                        <li><a href="/product-category/sensors-hubs/motion-sensors/">مستشعر حركة</a></li>
                        <li><a href="/product-category/sensors-hubs/door-window-sensors/">مستشعر باب/نافذة</a></li>
                        <li><a href="/product-category/sensors-hubs/temperature-humidity/">حرارة ورطوبة</a></li>
                        <li><a href="/product-category/sensors-hubs/hubs-controllers/">هبات ومراكز</a></li>
                    </ul>
                </div>
                <div class="neogen-mega-col">
                    <h4><a href="/product-category/switches-electrical/">المفاتيح والكهربائيات</a></h4>
                    <ul>
                        <li><a href="/product-category/switches-electrical/smart-switches/">مفاتيح ذكية</a></li>
                        <li><a href="/product-category/switches-electrical/smart-plugs/">بلاقات ذكية</a></li>
                        <li><a href="/product-category/switches-electrical/relays-breakers/">ريلاي وقواطع</a></li>
                        <li><a href="/product-category/switches-electrical/dimmers/">دمرات</a></li>
                    </ul>
                </div>
                <div class="neogen-mega-col">
                    <h4><a href="/product-category/climate-energy/">التحكم بالمناخ</a></h4>
                    <ul>
                        <li><a href="/product-category/climate-energy/ac-controllers/">تحكم مكيف</a></li>
                        <li><a href="/product-category/climate-energy/thermostats/">ثرموستات</a></li>
                        <li><a href="/product-category/climate-energy/smart-curtains/">ستائر ذكية</a></li>
                    </ul>
                </div>
                <div class="neogen-mega-col">
                    <h4><a href="/product-category/networking-audio/">الشبكات والصوت</a></h4>
                    <ul>
                        <li><a href="/product-category/networking-audio/routers-mesh/">راوترات</a></li>
                        <li><a href="/product-category/networking-audio/access-points/">أكسس بوينت</a></li>
                        <li><a href="/product-category/networking-audio/smart-speakers/">مكبرات صوت ذكية</a></li>
                    </ul>
                </div>
            </div>
        </div>';
    }

    // =========================================================
    // MOBILE BOTTOM NAV HTML
    // =========================================================
    private function get_mobile_nav_html() {
        return '
        <nav id="neogen-mobile-nav" class="neogen-mobile-nav">
            <a href="/" class="neogen-mnav-item">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>الرئيسية</span>
            </a>
            <a href="/shop/" class="neogen-mnav-item">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                <span>التصنيفات</span>
            </a>
            <a href="/cart/" class="neogen-mnav-item neogen-mnav-cart">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                <span>السلة</span>
                <span class="neogen-mnav-badge" style="display:none;"></span>
            </a>
            <a href="/my-account/" class="neogen-mnav-item">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>حسابي</span>
            </a>
        </nav>';
    }

    // =========================================================
    // CSS
    // =========================================================
    private function get_css() {
        return <<<'CSS'
/* ============================================
   NEOGEN THEME v2.0 - Blueprint Design System
   ============================================ */

/* --- CSS Custom Properties --- */
:root,
[data-theme="light"] {
    --neo-primary: #1A3A5C;
    --neo-primary-dark: #0D2137;
    --neo-secondary: #00BFA6;
    --neo-accent: #FF6B35;
    --neo-bg: #F8F9FA;
    --neo-bg-alt: #F0F2F5;
    --neo-surface: #FFFFFF;
    --neo-header-bg: #0D2137;
    --neo-footer-bg: #0D2137;
    --neo-text: #1A1A2E;
    --neo-text-secondary: #4B5563;
    --neo-text-muted: #6B7280;
    --neo-border: #E5E7EB;
    --neo-border-hover: #1A3A5C;
    --neo-success: #10B981;
    --neo-warning: #F59E0B;
    --neo-error: #EF4444;
    --neo-shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --neo-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    --neo-shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.1);
    --neo-radius: 12px;
    --neo-radius-sm: 8px;
    --neo-radius-lg: 16px;
    --neo-gradient: linear-gradient(135deg, #1A3A5C 0%, #0D2137 100%);
    --neo-gradient-secondary: linear-gradient(135deg, #00BFA6 0%, #00897B 100%);
}

[data-theme="dark"] {
    --neo-primary: #3B82F6;
    --neo-primary-dark: #1E3A5F;
    --neo-secondary: #00BFA6;
    --neo-accent: #FF6B35;
    --neo-bg: #0F172A;
    --neo-bg-alt: #1E293B;
    --neo-surface: #1E293B;
    --neo-header-bg: #020617;
    --neo-footer-bg: #020617;
    --neo-text: #F1F5F9;
    --neo-text-secondary: #CBD5E1;
    --neo-text-muted: #94A3B8;
    --neo-border: #334155;
    --neo-border-hover: #3B82F6;
    --neo-shadow-sm: 0 1px 2px rgba(0,0,0,0.2);
    --neo-shadow: 0 4px 6px -1px rgba(0,0,0,0.3);
    --neo-shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.4);
    --neo-gradient: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%);
    --neo-gradient-secondary: linear-gradient(135deg, #00BFA6 0%, #00897B 100%);
}

/* --- Typography --- */
body {
    font-family: 'IBM Plex Sans Arabic', 'Inter', -apple-system, sans-serif !important;
    font-size: 16px;
    line-height: 1.7;
    color: var(--neo-text);
    background: var(--neo-bg);
    -webkit-font-smoothing: antialiased;
}
h1, h2, h3, h4, h5, h6 {
    font-family: 'IBM Plex Sans Arabic', 'Inter', sans-serif !important;
    color: var(--neo-text);
    line-height: 1.4;
}
h1 { font-size: 2.25rem; font-weight: 700; }
h2 { font-size: 1.875rem; font-weight: 700; }
h3 { font-size: 1.5rem; font-weight: 600; }
h4 { font-size: 1.25rem; font-weight: 600; }
p { line-height: 1.8; color: var(--neo-text-secondary); }
a { color: var(--neo-primary); transition: color 0.2s; }
a:hover { color: var(--neo-secondary); }

/* --- Smooth Transitions --- */
*, *::before, *::after {
    transition: background-color 0.25s ease, color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}

/* ============================================
   PROMO BAR
   ============================================ */
#neogen-promo-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 99999;
    background: var(--neo-gradient-secondary);
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    padding: 10px 16px;
    font-family: 'IBM Plex Sans Arabic', sans-serif;
}
#neogen-promo-bar .neogen-promo-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    max-width: 1200px;
    margin: 0 auto;
}
#neogen-promo-bar .neogen-promo-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    padding: 0 4px;
    opacity: 0.8;
    line-height: 1;
}
#neogen-promo-bar .neogen-promo-close:hover { opacity: 1; }
body.has-promo-bar { padding-top: 44px !important; }
body.has-promo-bar .site-header,
body.has-promo-bar .ct-header,
body.has-promo-bar header { top: 44px !important; }
@media (max-width: 767px) {
    #neogen-promo-bar { font-size: 12px; padding: 8px 12px; }
    #neogen-promo-bar .neogen-promo-text { font-size: 11px; }
    body.has-promo-bar { padding-top: 40px !important; }
    body.has-promo-bar .site-header,
    body.has-promo-bar .ct-header,
    body.has-promo-bar header { top: 40px !important; }
}

/* ============================================
   HEADER
   ============================================ */
header, .site-header, .ct-header, #masthead {
    background: var(--neo-header-bg) !important;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
header a, .ct-header a, .site-header a { color: #fff !important; }
header a:hover, .ct-header a:hover { color: var(--neo-secondary) !important; }
.ct-cart-count, .cart-count {
    background: var(--neo-accent) !important;
    color: #fff !important;
    font-weight: 700;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    font-size: 11px;
}

/* ============================================
   MEGA MENU
   ============================================ */
.neogen-mega-menu {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 99990;
    background: var(--neo-surface);
    border-bottom: 3px solid var(--neo-secondary);
    box-shadow: var(--neo-shadow-lg);
    padding: 0;
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.25s ease, transform 0.25s ease;
    pointer-events: none;
}
.neogen-mega-menu.active {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
    display: block !important;
}
.neogen-mega-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 32px 24px;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 24px;
}
.neogen-mega-col h4 {
    font-size: 15px;
    font-weight: 700;
    color: var(--neo-primary);
    margin: 0 0 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--neo-secondary);
}
.neogen-mega-col h4 a {
    color: var(--neo-primary) !important;
    text-decoration: none;
}
.neogen-mega-col h4 a:hover { color: var(--neo-secondary) !important; }
.neogen-mega-col ul {
    list-style: none;
    margin: 0;
    padding: 0;
}
.neogen-mega-col ul li {
    margin-bottom: 8px;
}
.neogen-mega-col ul li a {
    color: var(--neo-text-secondary) !important;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s, padding 0.2s;
    display: block;
}
.neogen-mega-col ul li a:hover {
    color: var(--neo-secondary) !important;
    padding-inline-start: 4px;
}
@media (max-width: 767px) {
    .neogen-mega-inner {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        padding: 20px 16px;
        max-height: 70vh;
        overflow-y: auto;
    }
}

/* ============================================
   BUTTONS
   ============================================ */
.button, .btn, .wp-block-button__link,
.woocommerce a.button, .woocommerce button.button,
.ct-button, .elementor-button,
button[type="submit"], input[type="submit"],
.single_add_to_cart_button {
    background: var(--neo-gradient) !important;
    color: #fff !important;
    border: none !important;
    border-radius: var(--neo-radius-sm) !important;
    padding: 12px 28px !important;
    font-family: 'IBM Plex Sans Arabic', sans-serif !important;
    font-weight: 600 !important;
    font-size: 15px !important;
    box-shadow: var(--neo-shadow-sm) !important;
    transition: all 0.25s ease !important;
    cursor: pointer;
}
.button:hover, .btn:hover,
.woocommerce a.button:hover, .woocommerce button.button:hover,
.elementor-button:hover, .single_add_to_cart_button:hover {
    transform: translateY(-2px) !important;
    box-shadow: var(--neo-shadow-lg) !important;
    opacity: 0.95;
}
.woocommerce a.button.alt, .woocommerce button.button.alt,
.checkout-button, .single_add_to_cart_button {
    background: var(--neo-gradient-secondary) !important;
}
.woocommerce a.added_to_cart {
    color: var(--neo-secondary) !important;
    font-weight: 600;
}

/* ============================================
   PRODUCT CARDS (Shop Grid)
   ============================================ */
.woocommerce ul.products li.product,
.ct-woo-card-inner {
    background: var(--neo-surface) !important;
    border: 1px solid var(--neo-border) !important;
    border-radius: var(--neo-radius-lg) !important;
    overflow: hidden;
    transition: all 0.3s ease !important;
    position: relative;
}
.woocommerce ul.products li.product:hover,
.ct-woo-card-inner:hover {
    transform: translateY(-6px);
    box-shadow: var(--neo-shadow-lg) !important;
    border-color: var(--neo-border-hover) !important;
}
.woocommerce ul.products li.product .woocommerce-loop-product__title,
.woocommerce ul.products li.product .woocommerce-loop-product__title a {
    color: var(--neo-text) !important;
    font-family: 'IBM Plex Sans Arabic', sans-serif !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    line-height: 1.5;
}
.woocommerce ul.products li.product .price,
.woocommerce ul.products li.product .price .amount {
    color: var(--neo-primary) !important;
    font-weight: 700 !important;
    font-size: 1.15rem !important;
    font-family: 'IBM Plex Sans Arabic', sans-serif !important;
}
.woocommerce ul.products li.product .price del {
    color: var(--neo-text-muted) !important;
    font-size: 0.85rem !important;
}
.woocommerce ul.products li.product .price ins {
    color: var(--neo-error) !important;
    text-decoration: none !important;
}
.woocommerce span.onsale {
    background: var(--neo-accent) !important;
    color: #fff !important;
    border-radius: var(--neo-radius-sm) !important;
    font-family: 'IBM Plex Sans Arabic', sans-serif !important;
    font-weight: 600;
    font-size: 13px;
    padding: 4px 12px !important;
    min-height: auto !important;
    line-height: 1.5 !important;
}
.woocommerce ul.products li.product .button,
.woocommerce ul.products li.product .add_to_cart_button {
    background: var(--neo-gradient) !important;
    border-radius: var(--neo-radius-sm) !important;
    font-size: 13px !important;
    padding: 10px 20px !important;
}

/* Product card image */
.woocommerce ul.products li.product a img,
.ct-woo-card-inner .ct-media-container img {
    border-radius: 0 !important;
    transition: transform 0.3s ease;
}
.woocommerce ul.products li.product:hover a img {
    transform: scale(1.03);
}

/* ============================================
   SHOP PAGE GRID LAYOUT
   Override Blocksy [data-products] + Elementor .elementor-grid
   ============================================ */
/* Override Blocksy CSS variables that control the grid */
[data-products],
[data-products].columns-3,
[data-products].columns-4,
.woocommerce ul.products,
ul.products.elementor-grid {
    --shop-columns: repeat(4, minmax(0, 1fr)) !important;
    --grid-columns-gap: 24px !important;
    --grid-rows-gap: 24px !important;
    display: grid !important;
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 24px !important;
    column-gap: 24px !important;
    row-gap: 24px !important;
    padding: 0 !important;
    margin: 0 0 40px 0 !important;
    list-style: none !important;
}
[data-products] .product,
.woocommerce ul.products li.product {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}
/* Hide Elementor archive template block rendered inside product grid */
.woocommerce ul.products > .elementor,
.woocommerce ul.products > [data-elementor-type],
.woocommerce ul.products > .e-con,
[data-products] > .elementor,
[data-products] > [data-elementor-type] {
    display: none !important;
}
/* Product card inner structure */
[data-products] .product .ct-woo-card-inner,
.woocommerce ul.products li.product .ct-woo-card-inner {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.woocommerce ul.products li.product .ct-image-container,
.woocommerce ul.products li.product > a > img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: contain;
    background: var(--neo-bg-alt, #f8f9fa);
}
[data-products] .product .ct-media-container,
.woocommerce ul.products li.product .ct-woo-card-inner .ct-media-container {
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--neo-bg-alt, #f8f9fa);
}
[data-products] .product .ct-media-container img,
.woocommerce ul.products li.product .ct-woo-card-inner .ct-media-container img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
/* Add to cart button full width */
[data-products] .product .button,
.woocommerce ul.products li.product .button,
.woocommerce ul.products li.product .add_to_cart_button {
    width: 100% !important;
    text-align: center !important;
    margin-top: auto !important;
}
/* Pagination */
.woocommerce nav.woocommerce-pagination {
    margin: 40px 0 !important;
    text-align: center;
}
.woocommerce nav.woocommerce-pagination ul {
    display: flex !important;
    justify-content: center !important;
    gap: 6px !important;
    border: none !important;
}
/* Responsive grid breakpoints */
@media (max-width: 1024px) {
    [data-products],
    [data-products].columns-3,
    .woocommerce ul.products,
    ul.products.elementor-grid {
        --shop-columns: repeat(3, minmax(0, 1fr)) !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 16px !important;
        --grid-columns-gap: 16px !important;
        --grid-rows-gap: 16px !important;
    }
}
@media (max-width: 767px) {
    [data-products],
    [data-products].columns-3,
    .woocommerce ul.products,
    ul.products.elementor-grid {
        --shop-columns: repeat(2, minmax(0, 1fr)) !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 12px !important;
        --grid-columns-gap: 12px !important;
        --grid-rows-gap: 12px !important;
    }
}
@media (max-width: 400px) {
    [data-products],
    .woocommerce ul.products {
        gap: 10px !important;
        --grid-columns-gap: 10px !important;
        --grid-rows-gap: 10px !important;
    }
    .woocommerce ul.products li.product .woocommerce-loop-product__title {
        font-size: 13px !important;
    }
}

/* ============================================
   SHOP PAGE - LAYOUT + FILTERS
   ============================================ */
.woocommerce .woocommerce-result-count {
    color: var(--neo-text-muted);
    font-family: 'IBM Plex Sans Arabic', sans-serif;
}
.woocommerce .woocommerce-ordering {
    margin-bottom: 20px;
}
.woocommerce .woocommerce-ordering select {
    background: var(--neo-surface);
    border: 2px solid var(--neo-border);
    border-radius: var(--neo-radius-sm);
    padding: 8px 16px;
    color: var(--neo-text);
    font-family: 'IBM Plex Sans Arabic', sans-serif;
}
/* Category description on archive */
.woocommerce .term-description,
.woocommerce-products-header__description {
    background: var(--neo-surface);
    border-radius: var(--neo-radius);
    padding: 20px 24px;
    margin-bottom: 24px;
    border-right: 4px solid var(--neo-secondary);
    color: var(--neo-text-secondary);
    font-size: 15px;
    line-height: 1.8;
}
/* Pagination */
.woocommerce nav.woocommerce-pagination ul li a,
.woocommerce nav.woocommerce-pagination ul li span {
    border-radius: var(--neo-radius-sm) !important;
    border: 1px solid var(--neo-border) !important;
    color: var(--neo-text) !important;
    background: var(--neo-surface) !important;
    margin: 0 4px;
    padding: 8px 14px !important;
    font-weight: 500;
}
.woocommerce nav.woocommerce-pagination ul li span.current {
    background: var(--neo-primary) !important;
    color: #fff !important;
    border-color: var(--neo-primary) !important;
}
.woocommerce nav.woocommerce-pagination ul li a:hover {
    background: var(--neo-bg-alt) !important;
    border-color: var(--neo-primary) !important;
}

/* Shop sidebar filter drawer (injected by JS) */
.neogen-shop-filters {
    background: var(--neo-surface);
    border: 1px solid var(--neo-border);
    border-radius: var(--neo-radius);
    padding: 24px;
    margin-bottom: 24px;
}
.neogen-shop-filters h3 {
    font-size: 18px;
    color: var(--neo-text);
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--neo-secondary);
}
.neogen-filter-group { margin-bottom: 20px; }
.neogen-filter-group h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--neo-text);
    margin: 0 0 10px;
}
.neogen-filter-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    color: var(--neo-text-secondary);
    font-size: 14px;
    cursor: pointer;
}
.neogen-filter-group input[type="checkbox"] {
    accent-color: var(--neo-secondary);
    width: 18px;
    height: 18px;
}

/* Mobile filter button */
.neogen-filter-toggle {
    display: none;
    background: var(--neo-surface) !important;
    color: var(--neo-text) !important;
    border: 2px solid var(--neo-border) !important;
    border-radius: var(--neo-radius-sm) !important;
    padding: 10px 20px !important;
    font-size: 14px !important;
    margin-bottom: 16px;
    width: 100%;
}
@media (max-width: 767px) {
    .neogen-filter-toggle { display: block; }
    .neogen-shop-filters {
        position: fixed;
        top: 0;
        right: -100%;
        bottom: 0;
        z-index: 99998;
        width: 85%;
        max-width: 360px;
        border-radius: 0;
        overflow-y: auto;
        transition: right 0.3s ease;
        box-shadow: var(--neo-shadow-lg);
    }
    [dir="ltr"] .neogen-shop-filters {
        right: auto;
        left: -100%;
        transition: left 0.3s ease;
    }
    .neogen-shop-filters.open { right: 0; }
    [dir="ltr"] .neogen-shop-filters.open { left: 0; }
    .neogen-filter-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 99997;
        display: none;
    }
    .neogen-filter-overlay.open { display: block; }
}

/* ============================================
   SINGLE PRODUCT PAGE
   ============================================ */
.woocommerce div.product .product_title {
    color: var(--neo-text) !important;
    font-size: 1.75rem;
    font-weight: 700;
}
.woocommerce div.product p.price,
.woocommerce div.product span.price {
    color: var(--neo-primary) !important;
    font-size: 1.75rem !important;
    font-weight: 700 !important;
}
.woocommerce div.product p.price del { color: var(--neo-text-muted) !important; font-size: 1.1rem !important; }
.woocommerce div.product p.price ins { color: var(--neo-error) !important; text-decoration: none; }
.woocommerce div.product .woocommerce-product-details__short-description {
    color: var(--neo-text-secondary);
    line-height: 1.8;
    font-size: 15px;
}

/* Product meta (categories, tags) */
.woocommerce div.product .product_meta {
    color: var(--neo-text-muted);
    font-size: 14px;
    padding-top: 16px;
    border-top: 1px solid var(--neo-border);
    margin-top: 16px;
}
.woocommerce div.product .product_meta a { color: var(--neo-secondary) !important; }

/* Trust badges (injected by JS) */
.neogen-trust-badges {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin: 20px 0;
    padding: 20px;
    background: var(--neo-bg);
    border-radius: var(--neo-radius);
}
.neogen-trust-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--neo-text-secondary);
}
.neogen-trust-badge svg {
    flex-shrink: 0;
    color: var(--neo-secondary);
}

/* Compatibility badges (injected by JS) */
.neogen-compat-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 16px 0;
}
.neogen-compat-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--neo-bg);
    border: 1px solid var(--neo-border);
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 500;
    color: var(--neo-text-secondary);
}
.neogen-compat-badge.active {
    background: rgba(0,191,166,0.1);
    border-color: var(--neo-secondary);
    color: var(--neo-secondary);
}

/* Tabs */
.woocommerce div.product .woocommerce-tabs ul.tabs {
    border-bottom: 2px solid var(--neo-border) !important;
    padding: 0 !important;
    margin: 0 0 24px !important;
}
.woocommerce div.product .woocommerce-tabs ul.tabs::before { border: none !important; }
.woocommerce div.product .woocommerce-tabs ul.tabs li {
    background: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    border-radius: 0 !important;
}
.woocommerce div.product .woocommerce-tabs ul.tabs li a {
    color: var(--neo-text-muted) !important;
    font-weight: 500 !important;
    padding: 12px 24px !important;
    display: block;
    border-bottom: 3px solid transparent;
    font-family: 'IBM Plex Sans Arabic', sans-serif !important;
}
.woocommerce div.product .woocommerce-tabs ul.tabs li.active a {
    color: var(--neo-primary) !important;
    font-weight: 600 !important;
    border-bottom-color: var(--neo-primary);
}
.woocommerce div.product .woocommerce-tabs ul.tabs li a:hover {
    color: var(--neo-primary) !important;
}
.woocommerce div.product .woocommerce-tabs .panel {
    background: var(--neo-surface);
    border-radius: var(--neo-radius);
    padding: 24px;
}

/* Quantity input */
.woocommerce .quantity .qty {
    border: 2px solid var(--neo-border) !important;
    border-radius: var(--neo-radius-sm) !important;
    padding: 10px !important;
    width: 80px;
    text-align: center;
    font-weight: 600;
    color: var(--neo-text) !important;
    background: var(--neo-surface) !important;
}

/* Related + upsells */
.woocommerce div.product .related h2,
.woocommerce div.product .upsells h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--neo-text);
    margin-bottom: 24px;
}

/* ============================================
   FORMS
   ============================================ */
input[type="text"], input[type="email"], input[type="tel"],
input[type="password"], input[type="number"], input[type="search"],
textarea, select, .select2-container .select2-selection {
    background: var(--neo-surface) !important;
    border: 2px solid var(--neo-border) !important;
    border-radius: var(--neo-radius-sm) !important;
    padding: 12px 16px !important;
    color: var(--neo-text) !important;
    font-family: 'IBM Plex Sans Arabic', sans-serif !important;
    font-size: 15px !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
}
input:focus, textarea:focus, select:focus {
    border-color: var(--neo-primary) !important;
    box-shadow: 0 0 0 3px rgba(26,58,92,0.1) !important;
    outline: none !important;
}

/* ============================================
   CART
   ============================================ */
.woocommerce-cart-form table {
    background: var(--neo-surface) !important;
    border-radius: var(--neo-radius-lg) !important;
    border: 1px solid var(--neo-border) !important;
    overflow: hidden;
}
.woocommerce-cart-form table th {
    background: var(--neo-bg) !important;
    color: var(--neo-text) !important;
    font-weight: 600;
    font-family: 'IBM Plex Sans Arabic', sans-serif;
    padding: 16px !important;
}
.woocommerce-cart-form table td {
    color: var(--neo-text) !important;
    border-color: var(--neo-border) !important;
    padding: 16px !important;
    vertical-align: middle;
}
.cart_totals {
    background: var(--neo-surface) !important;
    border-radius: var(--neo-radius-lg) !important;
    border: 1px solid var(--neo-border) !important;
    padding: 24px !important;
}
.cart_totals h2 {
    font-size: 1.25rem;
    color: var(--neo-text);
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--neo-secondary);
}
.cart_totals table td, .cart_totals table th {
    color: var(--neo-text) !important;
    padding: 12px 0 !important;
}
.woocommerce-cart .cart-empty {
    text-align: center;
    font-size: 1.1rem;
    color: var(--neo-text-muted);
    padding: 60px 20px;
}

/* Coupon */
.woocommerce .coupon input[type="text"] {
    width: auto !important;
}

/* ============================================
   CHECKOUT
   ============================================ */
.woocommerce-checkout .woocommerce-billing-fields h3,
.woocommerce-checkout .woocommerce-shipping-fields h3,
.woocommerce-checkout .woocommerce-additional-fields h3 {
    font-size: 1.25rem;
    color: var(--neo-text);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--neo-secondary);
}
.woocommerce-checkout .form-row label {
    color: var(--neo-text) !important;
    font-weight: 500;
    font-size: 14px;
    margin-bottom: 4px;
}
.woocommerce-checkout-review-order {
    background: var(--neo-surface) !important;
    border-radius: var(--neo-radius-lg) !important;
    border: 1px solid var(--neo-border) !important;
    padding: 24px !important;
}
.woocommerce-checkout-review-order table {
    color: var(--neo-text);
}
.woocommerce-checkout-review-order table th {
    color: var(--neo-text) !important;
    font-weight: 600;
}
#payment {
    background: var(--neo-surface) !important;
    border-radius: var(--neo-radius-lg) !important;
    border: 1px solid var(--neo-border) !important;
}
#payment .payment_methods li {
    border-bottom: 1px solid var(--neo-border) !important;
    padding: 16px !important;
}
#payment .payment_methods li label {
    color: var(--neo-text) !important;
    font-weight: 500;
}
.woocommerce #payment #place_order {
    width: 100%;
    padding: 16px !important;
    font-size: 18px !important;
    background: var(--neo-gradient-secondary) !important;
}

/* ============================================
   BREADCRUMBS
   ============================================ */
.woocommerce .woocommerce-breadcrumb,
.ct-breadcrumbs {
    color: var(--neo-text-muted) !important;
    font-size: 13px;
    margin-bottom: 16px;
}
.woocommerce .woocommerce-breadcrumb a,
.ct-breadcrumbs a {
    color: var(--neo-text-muted) !important;
}
.woocommerce .woocommerce-breadcrumb a:hover,
.ct-breadcrumbs a:hover {
    color: var(--neo-secondary) !important;
}

/* ============================================
   MY ACCOUNT
   ============================================ */
.woocommerce-MyAccount-navigation ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.woocommerce-MyAccount-navigation ul li {
    margin-bottom: 4px;
}
.woocommerce-MyAccount-navigation ul li a {
    display: block;
    padding: 12px 20px;
    background: var(--neo-surface);
    border: 1px solid var(--neo-border);
    border-radius: var(--neo-radius-sm);
    color: var(--neo-text) !important;
    font-weight: 500;
    transition: all 0.2s;
}
.woocommerce-MyAccount-navigation ul li.is-active a {
    background: var(--neo-primary);
    color: #fff !important;
    border-color: var(--neo-primary);
}
.woocommerce-MyAccount-navigation ul li a:hover {
    border-color: var(--neo-primary);
    color: var(--neo-primary) !important;
}

/* ============================================
   FOOTER
   ============================================ */
footer, .site-footer, #colophon, .ct-footer {
    background: var(--neo-footer-bg) !important;
    color: rgba(255,255,255,0.9) !important;
}
.site-footer a, .ct-footer a, footer a { color: rgba(255,255,255,0.7) !important; }
.site-footer a:hover, .ct-footer a:hover, footer a:hover { color: var(--neo-secondary) !important; }
.site-footer h4, .ct-footer h4, footer h4,
.site-footer .widget-title, .ct-footer .widget-title {
    color: #fff !important;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
}
footer .ct-widget p, .site-footer p { color: rgba(255,255,255,0.6) !important; }

/* ============================================
   ELEMENTOR OVERRIDES
   ============================================ */
section.elementor-section, .elementor-section { background: var(--neo-bg); }
h2.elementor-heading-title { color: var(--neo-text) !important; }
.elementor-widget-text-editor { color: var(--neo-text-secondary); }

/* ============================================
   HOMEPAGE LAYOUT FIXES v2
   ============================================ */

/* --- HERO: Fix gradient + add depth + product image bg --- */
.elementor-2745 .elementor-element.elementor-element-dfe6b9f,
section.elementor-section.elementor-element-dfe6b9f {
    background: linear-gradient(135deg, #1A3A5C 0%, #0D2137 100%) !important;
    background-image: linear-gradient(135deg, #1A3A5C 0%, #0D2137 100%) !important;
    background-color: #1A3A5C !important;
    position: relative;
    overflow: hidden;
}
.elementor-element-dfe6b9f::before {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0;
    width: 50%;
    background-image: url('/wp-content/uploads/NGS-APP-STR-0485.webp');
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center right;
    opacity: 0.1;
    pointer-events: none;
    z-index: 1;
}
.elementor-element-dfe6b9f::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse at 25% 75%, rgba(0,191,166,0.15) 0%, transparent 55%),
        radial-gradient(ellipse at 75% 25%, rgba(255,107,53,0.10) 0%, transparent 55%);
    pointer-events: none;
    z-index: 1;
}
.elementor-element-dfe6b9f .elementor-container,
.elementor-element-dfe6b9f .elementor-column-wrap,
.elementor-element-dfe6b9f .elementor-widget-wrap {
    position: relative;
    z-index: 2;
}
.elementor-element-dfe6b9f h1.elementor-heading-title {
    font-size: clamp(26px, 5vw, 48px) !important;
    text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    line-height: 1.3 !important;
}
.elementor-element-dfe6b9f h3.elementor-heading-title {
    text-shadow: 0 1px 4px rgba(0,0,0,0.3);
    opacity: 0.85;
    font-size: clamp(14px, 2.5vw, 18px) !important;
    line-height: 1.6 !important;
}
/* Hero primary CTA */
.elementor-element-dfe6b9f .elementor-button {
    background: linear-gradient(135deg, #00BFA6 0%, #009688 100%) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 16px !important;
    padding: 14px 36px !important;
    box-shadow: 0 4px 20px rgba(0,191,166,0.35) !important;
    letter-spacing: 0.3px;
}
.elementor-element-dfe6b9f .elementor-button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(0,191,166,0.5) !important;
}
/* Hero secondary CTA */
.elementor-element-dfe6b9f .elementor-inner-section .elementor-column:last-child .elementor-button {
    background: transparent !important;
    border: 2px solid rgba(255,255,255,0.6) !important;
    box-shadow: none !important;
}
.elementor-element-dfe6b9f .elementor-inner-section .elementor-column:last-child .elementor-button:hover {
    border-color: #fff !important;
    background: rgba(255,255,255,0.1) !important;
}

/* --- TRUST BAR: Compact desktop + mobile grid --- */
.elementor-element-539dc7e2 {
    border-bottom: 1px solid #E5E7EB;
}
.elementor-element-539dc7e2 .elementor-icon-box-wrapper {
    text-align: center;
}
.elementor-element-539dc7e2 .elementor-icon-box-icon {
    margin-bottom: 4px !important;
}

/* --- CATEGORY CARDS: Desktop polish --- */
.elementor-element-2237bad9 .elementor-column .elementor-widget-wrap,
.elementor-element-36647ce2 .elementor-column .elementor-widget-wrap {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
}
.elementor-element-2237bad9 .elementor-column .elementor-widget-wrap:hover,
.elementor-element-36647ce2 .elementor-column .elementor-widget-wrap:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

/* --- SECTION SPACING --- */
.elementor-element-265f8fad { padding-bottom: 0 !important; }
.elementor-element-2fde265 { padding-top: 0 !important; }
.elementor-element-695c1146 { padding-bottom: 0 !important; }

/* --- SECTION HEADING ACCENTS --- */
.elementor-element-695c1146 h2.elementor-heading-title,
.elementor-element-5109055b h2.elementor-heading-title,
.elementor-element-265f8fad h2.elementor-heading-title,
.elementor-element-352c792d h2.elementor-heading-title,
.elementor-element-106439e3 h2.elementor-heading-title {
    position: relative;
    display: inline-block;
    padding-bottom: 14px;
}
.elementor-element-695c1146 h2.elementor-heading-title::after,
.elementor-element-5109055b h2.elementor-heading-title::after,
.elementor-element-265f8fad h2.elementor-heading-title::after,
.elementor-element-352c792d h2.elementor-heading-title::after,
.elementor-element-106439e3 h2.elementor-heading-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: var(--neo-secondary, #00BFA6);
    border-radius: 2px;
}

/* --- BLOG CARDS --- */
.neo-blog-card {
    border: 1px solid var(--neo-border, #E5E7EB) !important;
    transition: transform 0.2s, box-shadow 0.2s !important;
}
.neo-blog-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
}

/* --- BRANDS MARQUEE --- */
.neo-brands-marquee {
    padding: 10px 0 !important;
    mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent) !important;
    -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent) !important;
}

/* =========== MOBILE OVERRIDES (<768px) =========== */
@media (max-width: 767px) {

    /* HERO mobile: compact + buttons side by side */
    .elementor-2745 .elementor-element.elementor-element-dfe6b9f,
    section.elementor-section.elementor-element-dfe6b9f {
        min-height: auto !important;
        height: auto !important;
        padding: 32px 16px 28px !important;
    }
    .elementor-element-dfe6b9f::before { display: none; }
    .elementor-element-dfe6b9f .elementor-widget-spacer { display: none !important; }
    .elementor-element-dfe6b9f h1.elementor-heading-title {
        font-size: 24px !important;
        margin-bottom: 8px !important;
    }
    .elementor-element-dfe6b9f h3.elementor-heading-title {
        font-size: 13px !important;
        line-height: 1.5 !important;
        margin-bottom: 16px !important;
    }
    .elementor-element-dfe6b9f .elementor-inner-section > .elementor-container,
    .elementor-element-dfe6b9f .elementor-inner-section .elementor-row {
        display: flex !important;
        flex-direction: row !important;
        gap: 10px !important;
    }
    .elementor-element-dfe6b9f .elementor-inner-section .elementor-column {
        width: auto !important;
        flex: 1 !important;
    }
    .elementor-element-dfe6b9f .elementor-button {
        padding: 12px 16px !important;
        font-size: 14px !important;
        width: 100%;
        text-align: center;
    }

    /* TRUST BAR mobile: 2x2 grid, very compact */
    .elementor-element-539dc7e2 {
        padding: 12px 10px !important;
    }
    .elementor-element-539dc7e2 > .elementor-container,
    .elementor-element-539dc7e2 .elementor-row {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px !important;
    }
    .elementor-element-539dc7e2 .elementor-column {
        width: 100% !important;
    }
    .elementor-element-539dc7e2 .elementor-icon-box-wrapper {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 8px !important;
        background: #F8F9FA;
        border-radius: 8px;
    }
    .elementor-element-539dc7e2 .elementor-icon-box-icon {
        margin: 0 !important;
        flex-shrink: 0;
    }
    .elementor-element-539dc7e2 .elementor-icon-box-icon i,
    .elementor-element-539dc7e2 .elementor-icon-box-icon svg {
        font-size: 20px !important;
        width: 20px !important;
        height: 20px !important;
    }
    .elementor-element-539dc7e2 .elementor-icon-box-content {
        text-align: start !important;
    }
    .elementor-element-539dc7e2 .elementor-icon-box-title {
        font-size: 11px !important;
        line-height: 1.3 !important;
        margin: 0 !important;
    }
    .elementor-element-539dc7e2 .elementor-icon-box-description {
        display: none !important;
    }

    /* CATEGORY HEADING mobile */
    .elementor-element-695c1146 {
        padding: 24px 12px 8px !important;
    }
    .elementor-element-695c1146 .elementor-widget-spacer { display: none !important; }
    .elementor-element-695c1146 h2.elementor-heading-title {
        font-size: 22px !important;
    }

    /* CATEGORY CARDS mobile: 3-column compact grid */
    .elementor-element-2237bad9 > .elementor-container,
    .elementor-element-2237bad9 .elementor-row,
    .elementor-element-36647ce2 > .elementor-container,
    .elementor-element-36647ce2 .elementor-row {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 8px !important;
    }
    .elementor-element-2237bad9 .elementor-column,
    .elementor-element-36647ce2 .elementor-column {
        width: 100% !important;
    }
    .elementor-element-2237bad9,
    .elementor-element-36647ce2 {
        padding: 4px 10px !important;
    }
    .elementor-element-2237bad9 .elementor-column .elementor-widget-wrap,
    .elementor-element-36647ce2 .elementor-column .elementor-widget-wrap {
        padding: 10px 4px !important;
        border-radius: 10px;
    }
    .elementor-element-2237bad9 .elementor-icon-box-icon i,
    .elementor-element-2237bad9 .elementor-icon-box-icon svg,
    .elementor-element-36647ce2 .elementor-icon-box-icon i,
    .elementor-element-36647ce2 .elementor-icon-box-icon svg {
        font-size: 28px !important;
        width: 28px !important;
        height: 28px !important;
    }
    .elementor-element-2237bad9 .elementor-icon-box-icon,
    .elementor-element-36647ce2 .elementor-icon-box-icon {
        margin-bottom: 4px !important;
    }
    .elementor-element-2237bad9 .elementor-icon-box-title,
    .elementor-element-36647ce2 .elementor-icon-box-title {
        font-size: 11px !important;
        line-height: 1.3 !important;
        margin: 0 !important;
    }
    .elementor-element-2237bad9 .elementor-icon-box-description,
    .elementor-element-36647ce2 .elementor-icon-box-description {
        display: none !important;
    }

    /* BEST SELLERS mobile */
    .elementor-element-5109055b {
        padding: 24px 10px !important;
    }
    .elementor-element-5109055b .elementor-widget-spacer { display: none !important; }
    .elementor-element-5109055b h2.elementor-heading-title {
        font-size: 22px !important;
    }

    /* WHY NEOGEN VALUES mobile: 2-col grid + less padding */
    .elementor-element-265f8fad {
        padding: 24px 0 0 !important;
    }
    .elementor-element-265f8fad .elementor-widget-spacer { display: none !important; }
    .elementor-element-2fde265 {
        padding: 8px 12px 30px !important;
    }
    .elementor-element-2fde265 > .elementor-container,
    .elementor-element-2fde265 .elementor-row {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
    }
    .elementor-element-2fde265 .elementor-column {
        width: 100% !important;
    }
    .elementor-element-2fde265 .elementor-icon-box-icon i,
    .elementor-element-2fde265 .elementor-icon-box-icon svg {
        font-size: 28px !important;
        width: 28px !important;
        height: 28px !important;
    }
    .elementor-element-2fde265 .elementor-icon-box-title {
        font-size: 14px !important;
    }
    .elementor-element-2fde265 .elementor-icon-box-description {
        font-size: 12px !important;
        line-height: 1.4 !important;
    }

    /* BUNDLE CTA mobile */
    .elementor-element-79dc3925 {
        padding: 28px 16px !important;
    }
    .elementor-element-79dc3925 .elementor-widget-spacer { display: none !important; }

    /* BLOG mobile */
    .elementor-element-352c792d {
        padding: 24px 12px !important;
    }
    .elementor-element-352c792d .elementor-widget-spacer { display: none !important; }
    .neo-blog-grid {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    .neo-blog-card img {
        height: 140px !important;
    }

    /* BRANDS mobile */
    .elementor-element-106439e3 {
        padding: 20px 0 !important;
    }
    .elementor-element-106439e3 .elementor-widget-spacer { display: none !important; }
    .elementor-element-106439e3 h2.elementor-heading-title {
        font-size: 20px !important;
    }

    /* Kill ALL spacer widgets on mobile homepage */
    .elementor-2745 .elementor-widget-spacer .elementor-spacer {
        height: 0 !important;
        min-height: 0 !important;
    }
}

/* ============================================
   NOTICES & MESSAGES
   ============================================ */
.woocommerce-message {
    border-top-color: var(--neo-success) !important;
    background: rgba(16,185,129,0.08);
    color: var(--neo-text);
    border-radius: var(--neo-radius-sm);
}
.woocommerce-error {
    border-top-color: var(--neo-error) !important;
    background: rgba(239,68,68,0.08);
    border-radius: var(--neo-radius-sm);
}
.woocommerce-info {
    border-top-color: var(--neo-primary) !important;
    background: rgba(26,58,92,0.08);
    border-radius: var(--neo-radius-sm);
}

/* ============================================
   MOBILE BOTTOM NAV
   ============================================ */
.neogen-mobile-nav {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 99995;
    background: var(--neo-surface);
    border-top: 1px solid var(--neo-border);
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    padding: 8px 0;
    padding-bottom: calc(8px + env(safe-area-inset-bottom, 0px));
}
.neogen-mobile-nav {
    display: none;
    grid-template-columns: repeat(4, 1fr);
}
.neogen-mnav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px 4px;
    color: var(--neo-text-muted) !important;
    text-decoration: none !important;
    font-size: 11px;
    font-weight: 500;
    font-family: 'IBM Plex Sans Arabic', sans-serif;
    min-height: 44px;
    position: relative;
    -webkit-tap-highlight-color: transparent;
}
.neogen-mnav-item:hover,
.neogen-mnav-item.active {
    color: var(--neo-primary) !important;
}
.neogen-mnav-item svg {
    width: 22px;
    height: 22px;
}
.neogen-mnav-badge {
    position: absolute;
    top: 2px;
    right: 50%;
    margin-right: -18px;
    background: var(--neo-accent);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
}
@media (max-width: 767px) {
    .neogen-mobile-nav { display: grid !important; }
    body { padding-bottom: 72px !important; }
}
@media (min-width: 768px) {
    .neogen-mobile-nav { display: none !important; }
}

/* ============================================
   THEME TOGGLE CONTROLS
   ============================================ */
.neogen-controls {
    position: fixed;
    bottom: 90px;
    left: 16px;
    z-index: 99996;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
[dir="rtl"] .neogen-controls { left: auto; right: 16px; }
.neogen-toggle-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid var(--neo-border);
    background: var(--neo-surface);
    color: var(--neo-text);
    cursor: pointer;
    font-size: 1.1rem;
    box-shadow: var(--neo-shadow);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.neogen-toggle-btn:hover {
    transform: scale(1.08);
    border-color: var(--neo-primary);
}
.lang-toggle {
    background: var(--neo-primary) !important;
    color: #fff !important;
    border: none !important;
    font-weight: 700;
    font-size: 0.8rem;
    font-family: 'IBM Plex Sans Arabic', sans-serif;
}
@media (max-width: 767px) {
    .neogen-controls { bottom: 80px; }
}

/* ============================================
   DARK MODE SCROLLBAR
   ============================================ */
[data-theme="dark"] ::-webkit-scrollbar { width: 8px; }
[data-theme="dark"] ::-webkit-scrollbar-track { background: var(--neo-bg); }
[data-theme="dark"] ::-webkit-scrollbar-thumb { background: var(--neo-border); border-radius: 4px; }

/* ============================================
   LOADING SKELETON
   ============================================ */
@keyframes neo-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
.neo-skeleton {
    background: linear-gradient(90deg, var(--neo-bg) 25%, var(--neo-bg-alt) 50%, var(--neo-bg) 75%);
    background-size: 200% 100%;
    animation: neo-shimmer 1.5s infinite;
    border-radius: var(--neo-radius-sm);
}

/* ============================================
   PRINT STYLES
   ============================================ */
@media print {
    #neogen-promo-bar, .neogen-controls, .neogen-mobile-nav,
    .neogen-mega-menu { display: none !important; }
}
CSS;
    }

    // =========================================================
    // JAVASCRIPT
    // =========================================================
    private function get_js() {
        return <<<'JS'
(function(){
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        // =============================================
        // PROMO BAR
        // =============================================
        var promoBar = document.getElementById('neogen-promo-bar');
        if (promoBar && !localStorage.getItem('neogen-promo-dismissed')) {
            promoBar.style.display = 'block';
            document.body.classList.add('has-promo-bar');
            var closeBtn = promoBar.querySelector('.neogen-promo-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    promoBar.style.display = 'none';
                    document.body.classList.remove('has-promo-bar');
                    localStorage.setItem('neogen-promo-dismissed', Date.now());
                });
            }
        }

        // =============================================
        // THEME TOGGLE + LANG TOGGLE
        // =============================================
        var controls = document.createElement('div');
        controls.className = 'neogen-controls';
        controls.innerHTML = '<button class="neogen-toggle-btn theme-toggle" title="Toggle Theme"></button><button class="neogen-toggle-btn lang-toggle">EN</button>';
        document.body.appendChild(controls);

        var themeBtn = controls.querySelector('.theme-toggle');
        var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        themeBtn.textContent = currentTheme === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
        themeBtn.addEventListener('click', function() {
            var t = document.documentElement.getAttribute('data-theme');
            var n = t === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', n);
            localStorage.setItem('neogen-theme', n);
            themeBtn.textContent = n === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
        });

        var langBtn = controls.querySelector('.lang-toggle');
        var currentLang = localStorage.getItem('neogen-lang') || 'ar';
        langBtn.textContent = currentLang === 'ar' ? 'EN' : '\u0639';
        langBtn.addEventListener('click', function() {
            var l = localStorage.getItem('neogen-lang') || 'ar';
            var nl = l === 'ar' ? 'en' : 'ar';
            document.documentElement.setAttribute('lang', nl);
            document.documentElement.setAttribute('dir', nl === 'en' ? 'ltr' : 'rtl');
            localStorage.setItem('neogen-lang', nl);
            langBtn.textContent = nl === 'ar' ? 'EN' : '\u0639';
        });

        // =============================================
        // MEGA MENU
        // =============================================
        var megaMenu = document.getElementById('neogen-mega-menu');
        if (megaMenu) {
            // Find the shop/store menu item
            var menuLinks = document.querySelectorAll('.ct-header a, header a, .site-header a');
            var shopLink = null;
            menuLinks.forEach(function(link) {
                var text = link.textContent.trim();
                var href = link.getAttribute('href') || '';
                if (text === 'المتجر' || text === 'Shop' || href.indexOf('/shop') !== -1) {
                    if (!shopLink && link.closest('nav, .ct-header, header')) {
                        shopLink = link;
                    }
                }
            });

            if (shopLink) {
                var menuItem = shopLink.closest('li') || shopLink;
                var hoverTimeout;

                menuItem.addEventListener('mouseenter', function(e) {
                    clearTimeout(hoverTimeout);
                    megaMenu.classList.add('active');
                    // Position below header
                    var header = document.querySelector('header, .site-header, .ct-header');
                    if (header) {
                        var rect = header.getBoundingClientRect();
                        megaMenu.style.top = (rect.bottom) + 'px';
                    }
                });
                menuItem.addEventListener('mouseleave', function() {
                    hoverTimeout = setTimeout(function() {
                        megaMenu.classList.remove('active');
                    }, 200);
                });
                megaMenu.addEventListener('mouseenter', function() {
                    clearTimeout(hoverTimeout);
                });
                megaMenu.addEventListener('mouseleave', function() {
                    megaMenu.classList.remove('active');
                });

                // Prevent default click on shop link (let mega menu show)
                shopLink.addEventListener('click', function(e) {
                    if (window.innerWidth > 767) {
                        // On desktop, toggle mega menu
                        if (megaMenu.classList.contains('active')) {
                            // If already open, go to shop page
                            return;
                        }
                        e.preventDefault();
                        megaMenu.classList.toggle('active');
                        var header = document.querySelector('header, .site-header, .ct-header');
                        if (header) {
                            megaMenu.style.top = header.getBoundingClientRect().bottom + 'px';
                        }
                    }
                });
            }

            // Close mega menu on click outside
            document.addEventListener('click', function(e) {
                if (!megaMenu.contains(e.target) && (!shopLink || !shopLink.contains(e.target))) {
                    megaMenu.classList.remove('active');
                }
            });
        }

        // =============================================
        // MOBILE NAV - Cart Badge
        // =============================================
        var mobileNav = document.getElementById('neogen-mobile-nav');
        if (mobileNav) {
            // Update cart badge
            function updateCartBadge() {
                var badge = mobileNav.querySelector('.neogen-mnav-badge');
                var headerCount = document.querySelector('.ct-cart-count, .cart-count');
                if (badge && headerCount) {
                    var count = parseInt(headerCount.textContent) || 0;
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }
            updateCartBadge();

            // Highlight active tab
            var currentPath = window.location.pathname;
            var navItems = mobileNav.querySelectorAll('.neogen-mnav-item');
            navItems.forEach(function(item) {
                var href = item.getAttribute('href');
                if (href === '/' && currentPath === '/') {
                    item.classList.add('active');
                } else if (href !== '/' && currentPath.indexOf(href) === 0) {
                    item.classList.add('active');
                }
            });

            // Listen for WooCommerce cart updates
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).on('added_to_cart removed_from_cart updated_cart_totals', function() {
                    setTimeout(updateCartBadge, 500);
                });
            }
        }

        // =============================================
        // PRODUCT PAGE: Trust Badges + Compat Icons
        // =============================================
        var addToCartForm = document.querySelector('.woocommerce div.product form.cart');
        if (addToCartForm) {
            // Trust Badges
            var trustHTML = '<div class="neogen-trust-badges">' +
                '<div class="neogen-trust-badge"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg><span>شحن مجاني فوق 300 ر.س</span></div>' +
                '<div class="neogen-trust-badge"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><span>ضمان جودة المنتج</span></div>' +
                '<div class="neogen-trust-badge"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg><span>دعم عربي 24/7</span></div>' +
                '<div class="neogen-trust-badge"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 105.64-11.36L1 10"/></svg><span>إرجاع خلال 15 يوم</span></div>' +
            '</div>';
            addToCartForm.insertAdjacentHTML('afterend', trustHTML);

            // Compatibility badges - read from product attributes
            var metaSection = document.querySelector('.product_meta');
            if (metaSection) {
                var compatPlatforms = [
                    { key: 'home-assistant', label: 'Home Assistant', icon: 'HA' },
                    { key: 'homekit', label: 'Apple HomeKit', icon: 'HK' },
                    { key: 'alexa', label: 'Amazon Alexa', icon: 'AL' },
                    { key: 'google-home', label: 'Google Home', icon: 'GH' }
                ];
                var pageText = document.querySelector('.woocommerce-product-details__short-description')?.textContent || '';
                pageText += ' ' + (document.querySelector('.woocommerce-Tabs-panel--description')?.textContent || '');
                pageText = pageText.toLowerCase();

                var badges = '';
                compatPlatforms.forEach(function(p) {
                    var isActive = pageText.indexOf(p.key.replace('-', ' ')) !== -1 ||
                                   pageText.indexOf(p.label.toLowerCase()) !== -1;
                    badges += '<span class="neogen-compat-badge' + (isActive ? ' active' : '') + '">' +
                        '<strong>' + p.icon + '</strong> ' + p.label + '</span>';
                });

                if (badges) {
                    var compatHTML = '<div class="neogen-compat-badges">' + badges + '</div>';
                    var shortDesc = document.querySelector('.woocommerce-product-details__short-description');
                    if (shortDesc) {
                        shortDesc.insertAdjacentHTML('afterend', compatHTML);
                    } else {
                        addToCartForm.insertAdjacentHTML('beforebegin', compatHTML);
                    }
                }
            }
        }

        // =============================================
        // SHOP PAGE: Filter Toggle (mobile)
        // =============================================
        var shopFilters = document.querySelector('.neogen-shop-filters');
        var filterToggle = document.querySelector('.neogen-filter-toggle');
        var filterOverlay = document.querySelector('.neogen-filter-overlay');
        if (filterToggle && shopFilters) {
            filterToggle.addEventListener('click', function() {
                shopFilters.classList.toggle('open');
                if (filterOverlay) filterOverlay.classList.toggle('open');
            });
            if (filterOverlay) {
                filterOverlay.addEventListener('click', function() {
                    shopFilters.classList.remove('open');
                    filterOverlay.classList.remove('open');
                });
            }
        }

    });
})();
JS;
    }
}

new Neogen_Theme_Customizer();
