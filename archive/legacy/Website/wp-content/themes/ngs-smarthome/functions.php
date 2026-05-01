<?php
/**
 * NGS Smart Home Theme Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 */
function ngs_theme_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title.
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support( 'post-thumbnails' );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus( array(
        'primary' => esc_html__( 'Primary Menu', 'ngs-smarthome' ),
        'footer'  => esc_html__( 'Footer Menu', 'ngs-smarthome' ),
    ) );

    // Add support for core custom logo.
    add_theme_support( 'custom-logo', array(
        'height'      => 250,
        'width'       => 250,
        'flex-width'  => true,
        'flex-height' => true,
    ) );

    // WooCommerce Support
    add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'ngs_theme_setup' );

/**
 * Enqueue scripts and styles.
 */
function ngs_scripts() {
    wp_enqueue_style( 'ngs-style', get_stylesheet_uri() );
    
    // Add Google Fonts (Tajawal)
    wp_enqueue_style( 'ngs-fonts', 'https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap', array(), null );

    wp_enqueue_style(
        'ngs-page-content',
        get_template_directory_uri() . '/assets/css/page-content.css',
        array( 'ngs-style' ),
        '1.0.0'
    );

    wp_enqueue_script(
        'ngs-theme-interactions',
        get_template_directory_uri() . '/assets/js/theme-interactions.js',
        array(),
        '1.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ngs_scripts' );

/**
 * Disable Admin Bar for non-admins (optional, cleaner for testing)
 */
function ngs_disable_admin_bar() {
    if ( ! current_user_can( 'administrator' ) ) {
        show_admin_bar( false );
    }
}
add_action( 'after_setup_theme', 'ngs_disable_admin_bar' );

/**
 * =============================================================================
 * AR/3D MODEL SUPPORT FOR PRODUCTS
 * =============================================================================
 */

/**
 * Add 3D Model Meta Box to Product Edit Screen
 */
function neogen_add_3d_model_meta_box() {
    add_meta_box(
        'neogen_3d_model_meta_box',
        '🌐 نموذج ثلاثي الأبعاد (AR)',
        'neogen_3d_model_meta_box_callback',
        'product',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'neogen_add_3d_model_meta_box' );

/**
 * 3D Model Meta Box Content
 */
function neogen_3d_model_meta_box_callback( $post ) {
    wp_nonce_field( 'neogen_3d_model_nonce', 'neogen_3d_model_nonce_field' );

    $model_url = get_post_meta( $post->ID, '_neogen_3d_model_url', true );
    $model_ios_url = get_post_meta( $post->ID, '_neogen_3d_model_ios_url', true );
    ?>
    <style>
        .neogen-3d-field { margin-bottom: 15px; }
        .neogen-3d-field label { display: block; margin-bottom: 5px; font-weight: 600; }
        .neogen-3d-field input { width: 100%; }
        .neogen-3d-field .description { font-size: 11px; color: #666; margin-top: 5px; }
        .neogen-ar-info { background: #e7f5e7; border: 1px solid #4caf50; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; }
        .neogen-ar-info strong { color: #2e7d32; }
    </style>

    <div class="neogen-ar-info">
        <strong>✨ ميزة الواقع المعزز (AR)</strong><br>
        أضف رابط نموذج 3D لتفعيل ميزة "شاهد في غرفتك" للعملاء.
    </div>

    <div class="neogen-3d-field">
        <label for="neogen_3d_model_url">رابط ملف GLB (Android/Desktop)</label>
        <input type="url" id="neogen_3d_model_url" name="neogen_3d_model_url"
               value="<?php echo esc_attr( $model_url ); ?>"
               placeholder="https://example.com/model.glb">
        <p class="description">صيغة .glb - للأندرويد والمتصفحات</p>
    </div>

    <div class="neogen-3d-field">
        <label for="neogen_3d_model_ios_url">رابط ملف USDZ (iPhone/iPad)</label>
        <input type="url" id="neogen_3d_model_ios_url" name="neogen_3d_model_ios_url"
               value="<?php echo esc_attr( $model_ios_url ); ?>"
               placeholder="https://example.com/model.usdz">
        <p class="description">صيغة .usdz - لأجهزة Apple (اختياري)</p>
    </div>

    <div style="background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 11px;">
        <strong>📥 مصادر النماذج المجانية:</strong><br>
        • <a href="https://sketchfab.com/search?type=models&features=downloadable&licenses=7c23a1ba438d4306920229c12afcb5f9" target="_blank">Sketchfab (CC License)</a><br>
        • Amazon/AliExpress (من صفحة المنتج)<br>
        • <a href="https://poly.pizza" target="_blank">Poly.pizza</a><br>
        • مواقع الشركات المصنعة
    </div>
    <?php
}

/**
 * Save 3D Model Meta Box Data
 */
function neogen_save_3d_model_meta( $post_id ) {
    // Verify nonce
    if ( ! isset( $_POST['neogen_3d_model_nonce_field'] ) ||
         ! wp_verify_nonce( $_POST['neogen_3d_model_nonce_field'], 'neogen_3d_model_nonce' ) ) {
        return;
    }

    // Check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save GLB URL
    if ( isset( $_POST['neogen_3d_model_url'] ) ) {
        $model_url = sanitize_url( $_POST['neogen_3d_model_url'] );
        update_post_meta( $post_id, '_neogen_3d_model_url', $model_url );
    }

    // Save USDZ URL (iOS)
    if ( isset( $_POST['neogen_3d_model_ios_url'] ) ) {
        $model_ios_url = sanitize_url( $_POST['neogen_3d_model_ios_url'] );
        update_post_meta( $post_id, '_neogen_3d_model_ios_url', $model_ios_url );
    }
}
add_action( 'save_post_product', 'neogen_save_3d_model_meta' );

/**
 * Add AR badge to product loop (shop page)
 */
function neogen_add_ar_badge_to_loop() {
    global $product;
    $model_url = get_post_meta( $product->get_id(), '_neogen_3d_model_url', true );

    if ( ! empty( $model_url ) ) {
        echo '<div class="ar-product-badge" style="position: absolute; top: 10px; left: 10px; background: linear-gradient(135deg, #059669, #10b981); color: #fff; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; display: flex; align-items: center; gap: 4px; z-index: 10;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M2 12h20"></path></svg>
            AR
        </div>';
    }
}
add_action( 'woocommerce_before_shop_loop_item_title', 'neogen_add_ar_badge_to_loop', 5 );

/**
 * Allow GLB and USDZ file uploads
 */
function neogen_allow_3d_uploads( $mimes ) {
    $mimes['glb'] = 'model/gltf-binary';
    $mimes['gltf'] = 'model/gltf+json';
    $mimes['usdz'] = 'model/vnd.usdz+zip';
    return $mimes;
}
add_filter( 'upload_mimes', 'neogen_allow_3d_uploads' );

/**
 * Add product data tab for AR info
 */
function neogen_add_ar_product_tab( $tabs ) {
    global $product;
    $model_url = get_post_meta( $product->get_id(), '_neogen_3d_model_url', true );

    if ( ! empty( $model_url ) ) {
        $tabs['ar_view'] = array(
            'title'    => '🌐 عرض AR',
            'priority' => 5,
            'callback' => 'neogen_ar_tab_content',
        );
    }

    return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'neogen_add_ar_product_tab' );

/**
 * AR Tab Content
 */
function neogen_ar_tab_content() {
    global $product;
    ?>
    <h2>شاهد المنتج بتقنية الواقع المعزز</h2>
    <p>استخدم هاتفك الذكي لمشاهدة هذا المنتج في غرفتك قبل الشراء!</p>

    <div style="background: linear-gradient(135deg, #059669, #10b981); color: #fff; padding: 20px; border-radius: 12px; text-align: center; margin: 20px 0;">
        <h3 style="color: #fff; margin-bottom: 10px;">📱 كيفية الاستخدام</h3>
        <ol style="text-align: right; max-width: 400px; margin: 0 auto;">
            <li>اضغط على زر "عرض 3D" أعلاه</li>
            <li>اضغط على "شاهد في غرفتك"</li>
            <li>وجّه الكاميرا نحو الأرض أو السطح</li>
            <li>شاهد المنتج بحجمه الحقيقي في مكانك!</li>
        </ol>
    </div>

    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
        <div style="flex: 1; min-width: 200px; background: #f8fafc; padding: 15px; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem;">🍎</div>
            <strong>iPhone / iPad</strong>
            <p style="font-size: 0.9rem; color: #666;">يعمل تلقائياً مع Safari</p>
        </div>
        <div style="flex: 1; min-width: 200px; background: #f8fafc; padding: 15px; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem;">🤖</div>
            <strong>Android</strong>
            <p style="font-size: 0.9rem; color: #666;">يتطلب تطبيق Google AR</p>
        </div>
        <div style="flex: 1; min-width: 200px; background: #f8fafc; padding: 15px; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem;">💻</div>
            <strong>الكمبيوتر</strong>
            <p style="font-size: 0.9rem; color: #666;">عرض 3D فقط (بدون AR)</p>
        </div>
    </div>
    <?php
}

/**
 * -----------------------------------------------------------------------------
 * Editor-Driven Shortcodes & Block Patterns
 * -----------------------------------------------------------------------------
 */

function ngs_is_woocommerce_active() {
    return class_exists( 'WooCommerce' );
}

function ngs_shortcode_notice( $message ) {
    return '<div class="ngs-shortcode-notice">' . esc_html( $message ) . '</div>';
}

function ngs_get_whatsapp_digits() {
    $primary = (string) get_option( 'ngs_whatsapp_number', '' );
    $legacy = (string) get_option( 'neogen_whatsapp_number', '' );
    $raw = '' !== $primary ? $primary : $legacy;

    return preg_replace( '/\D+/', '', $raw );
}

function ngs_has_whatsapp_number() {
    return strlen( ngs_get_whatsapp_digits() ) >= 9;
}

function ngs_get_whatsapp_link( $message = '' ) {
    $digits = ngs_get_whatsapp_digits();
    if ( strlen( $digits ) < 9 ) {
        return '#';
    }

    $base = 'https://wa.me/' . $digits;
    if ( '' === $message ) {
        return $base;
    }

    return $base . '?text=' . rawurlencode( $message );
}

function ngs_get_whatsapp_display() {
    if ( ! ngs_has_whatsapp_number() ) {
        return '+966 5X XXX XXXX';
    }

    return '+' . ngs_get_whatsapp_digits();
}

function ngs_best_sellers_shortcode( $atts ) {
    if ( ! ngs_is_woocommerce_active() ) {
        return ngs_shortcode_notice( 'قسم المنتجات الأكثر مبيعاً غير متاح حالياً لأن WooCommerce غير مفعّل.' );
    }

    $atts = shortcode_atts(
        array(
            'limit'   => 3,
            'columns' => 3,
        ),
        $atts,
        'ngs_best_sellers'
    );

    $limit   = max( 1, min( 12, absint( $atts['limit'] ) ) );
    $columns = max( 1, min( 4, absint( $atts['columns'] ) ) );

    $products_shortcode = sprintf(
        '[products limit="%d" columns="%d" best_selling="true"]',
        $limit,
        $columns
    );

    ob_start();
    ?>
    <section class="ngs-shortcode-section ngs-best-sellers-shortcode">
        <div class="ngs-shortcode-head">
            <h2>المنتجات الأكثر مبيعاً</h2>
            <p>المنتجات الأكثر طلباً من عملائنا حالياً.</p>
        </div>
        <div class="ngs-shortcode-body">
            <?php echo do_shortcode( $products_shortcode ); ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ngs_best_sellers', 'ngs_best_sellers_shortcode' );

function ngs_featured_categories_shortcode( $atts ) {
    if ( ! ngs_is_woocommerce_active() ) {
        return ngs_shortcode_notice( 'قسم أقسام المنتجات غير متاح حالياً لأن WooCommerce غير مفعّل.' );
    }

    $atts = shortcode_atts(
        array(
            'slugs' => 'security,lighting,climate,automation,audio,irrigation',
        ),
        $atts,
        'ngs_featured_categories'
    );

    $slugs = array_filter(
        array_map(
            'sanitize_title',
            array_map( 'trim', explode( ',', (string) $atts['slugs'] ) )
        )
    );

    if ( empty( $slugs ) ) {
        return ngs_shortcode_notice( 'لم يتم تحديد أقسام صالحة في الشورت كود.' );
    }

    $items = array();
    foreach ( $slugs as $slug ) {
        $term = get_term_by( 'slug', $slug, 'product_cat' );
        if ( ! $term || is_wp_error( $term ) ) {
            continue;
        }

        $items[] = array(
            'name'  => $term->name,
            'count' => (int) $term->count,
            'link'  => get_term_link( $term ),
            'desc'  => wp_trim_words( wp_strip_all_tags( (string) $term->description ), 14, '...' ),
        );
    }

    if ( empty( $items ) ) {
        return ngs_shortcode_notice( 'لم يتم العثور على الأقسام المطلوبة. تأكد من Slugs أقسام المنتجات.' );
    }

    ob_start();
    ?>
    <section class="ngs-shortcode-section ngs-featured-categories-shortcode">
        <div class="ngs-shortcode-head">
            <h2>الأقسام الرئيسية</h2>
            <p>تصفح حسب الفئة المناسبة لاحتياج بيتك الذكي.</p>
        </div>
        <div class="ngs-featured-categories-grid">
            <?php foreach ( $items as $item ) : ?>
                <article class="ngs-category-tile">
                    <h3><?php echo esc_html( $item['name'] ); ?></h3>
                    <?php if ( ! empty( $item['desc'] ) ) : ?>
                        <p><?php echo esc_html( $item['desc'] ); ?></p>
                    <?php else : ?>
                        <p>منتجات مختارة ضمن هذه الفئة.</p>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $item['link'] ); ?>">تصفح القسم (<?php echo esc_html( $item['count'] ); ?>)</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ngs_featured_categories', 'ngs_featured_categories_shortcode' );

function ngs_trust_badges_shortcode() {
    ob_start();
    ?>
    <section class="ngs-shortcode-section ngs-trust-badges-shortcode">
        <div class="ngs-shortcode-head">
            <h2>الثقة والأمان</h2>
        </div>
        <ul class="ngs-trust-badges-list">
            <li>✓ مسجل لدى وزارة التجارة</li>
            <li>✓ ضريبة مضافة مشمولة</li>
            <li>✓ دفع آمن 100%</li>
            <li>✓ استرجاع سهل خلال 15 يوم</li>
        </ul>
        <div class="ngs-payment-badges">
            <span>مدى</span>
            <span>Apple Pay</span>
            <span>تابي</span>
            <span>تمارا</span>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ngs_trust_badges', 'ngs_trust_badges_shortcode' );

function ngs_whatsapp_cta_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'title'   => 'محتار من وين تبدأ؟',
            'text'    => 'تواصل معنا على واتساب ونساعدك تختار المنتجات المناسبة لبيتك.',
            'button'  => 'تواصل واتساب',
            'message' => 'مرحباً، أحتاج مساعدة في اختيار المنتجات المناسبة لبيتي.',
        ),
        $atts,
        'ngs_whatsapp_cta'
    );

    $has_whatsapp = ngs_has_whatsapp_number();
    $cta_url = ngs_get_whatsapp_link( (string) $atts['message'] );

    ob_start();
    ?>
    <section class="ngs-shortcode-section ngs-whatsapp-cta-shortcode">
        <h2><?php echo esc_html( $atts['title'] ); ?></h2>
        <p><?php echo esc_html( $atts['text'] ); ?></p>
        <a class="btn btn-primary<?php echo $has_whatsapp ? '' : ' is-disabled'; ?>" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $has_whatsapp ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <?php echo esc_html( $atts['button'] ); ?>
        </a>
        <?php if ( ! $has_whatsapp ) : ?>
            <p class="ngs-shortcode-placeholder">Placeholder: حدّث رقم واتساب من إعدادات الإضافة.</p>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ngs_whatsapp_cta', 'ngs_whatsapp_cta_shortcode' );

function ngs_register_block_patterns() {
    if ( ! function_exists( 'register_block_pattern' ) ) {
        return;
    }

    if ( function_exists( 'register_block_pattern_category' ) ) {
        register_block_pattern_category(
            'ngs-drafts',
            array( 'label' => __( 'NGS Drafts', 'ngs-smarthome' ) )
        );
    }

    $shop_url = esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );

    $patterns = array(
        'home-hero-ar' => array(
            'title'   => 'Home Hero (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading {"level":1} --><h1>حوّل بيتك إلى بيت ذكي<br>أسهل مما تتخيل!</h1><!-- /wp:heading --><!-- wp:paragraph --><p>منتجات ذكية عالية الجودة | دعم فني بالعربي 24/7 | شحن مجاني فوق 300 ريال</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . $shop_url . '">تسوق الآن</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->',
        ),
        'why-choose-us-ar' => array(
            'title'   => 'Why Choose Us (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading --><h2>لماذا نحن؟</h2><!-- /wp:heading --><!-- wp:list --><ul><li>خبراء Home Assistant في السعودية</li><li>دعم فني بالعربي على واتساب</li><li>شروحات مجانية على يوتيوب</li><li>ضمان سنة كاملة</li><li>استرجاع خلال 15 يوم</li><li>شحن سريع لجميع مناطق المملكة</li></ul><!-- /wp:list --></div><!-- /wp:group -->',
        ),
        'home-testimonials-ar' => array(
            'title'   => 'Testimonials (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading --><h2>شهادات العملاء</h2><!-- /wp:heading --><!-- wp:quote --><blockquote class="wp-block-quote"><p>"حولت شقتي لبيت ذكي بأقل من 2000 ريال! الدعم الفني ممتاز والشروحات واضحة"</p><cite>أحمد من الرياض ⭐⭐⭐⭐⭐</cite></blockquote><!-- /wp:quote --><!-- wp:quote --><blockquote class="wp-block-quote"><p>"أخيراً لقيت متجر يفهم Home Assistant ويتكلم عربي!"</p><cite>سارة من جدة ⭐⭐⭐⭐⭐</cite></blockquote><!-- /wp:quote --><!-- wp:quote --><blockquote class="wp-block-quote"><p>"الشحن وصل في يومين والتركيب كان سهل مع الفيديوهات المرفقة"</p><cite>محمد من الدمام ⭐⭐⭐⭐⭐</cite></blockquote><!-- /wp:quote --></div><!-- /wp:group -->',
        ),
        'how-it-works-ar' => array(
            'title'   => 'How It Works (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading --><h2>كيف نعمل</h2><!-- /wp:heading --><!-- wp:list {"ordered":true} --><ol><li>تصفح المنتجات أو الباقات المناسبة.</li><li>تأكد من التوافق مع نظامك الذكي.</li><li>أكمل الطلب عبر وسائل الدفع الآمنة.</li><li>يصلك الطلب مع شحن سريع داخل المملكة.</li><li>ركّب الأجهزة باتباع الشروحات والدعم الفني.</li><li>استفد من الضمان وخدمة ما بعد البيع.</li></ol><!-- /wp:list --></div><!-- /wp:group -->',
        ),
        'starter-bundle-ar' => array(
            'title'   => 'Starter Bundle Landing (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading --><h2>باقة المبتدئين لـ Home Assistant</h2><!-- /wp:heading --><!-- wp:paragraph --><p>ابدأ رحلتك في عالم البيوت الذكية مع باقة متكاملة بسعر مناسب.</p><!-- /wp:paragraph --><!-- wp:list {"ordered":true} --><ol><li>Aqara M3 Hub</li><li>حساس باب/نافذة × 2</li><li>حساس حرارة ورطوبة</li><li>حساس حركة</li><li>مقبس ذكي</li></ol><!-- /wp:list --><!-- wp:paragraph --><p><strong>إجمالي القيمة:</strong> 906 ريال | <strong>سعر الباقة:</strong> 699 ريال</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
        ),
        'security-bundle-ar' => array(
            'title'   => 'Security Bundle Landing (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading --><h2>باقة الحماية الذكية</h2><!-- /wp:heading --><!-- wp:paragraph --><p>أمّن بيتك وراقبه من أي مكان بباقات حماية متكاملة.</p><!-- /wp:paragraph --><!-- wp:list {"ordered":true} --><ol><li>كاميرا Aqara G3</li><li>قفل ذكي بالبصمة</li><li>حساس باب × 2</li><li>حساس حركة × 2</li><li>صفارة إنذار ذكية</li></ol><!-- /wp:list --><!-- wp:paragraph --><p><strong>إجمالي القيمة:</strong> 1,505 ريال | <strong>سعر الباقة:</strong> 1,199 ريال</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
        ),
        'security-category-ar' => array(
            'title'   => 'Security Category (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading --><h2>الأمان والحماية</h2><!-- /wp:heading --><!-- wp:list --><ul><li>كاميرات ذكية برؤية ليلية</li><li>أقفال ذكية بالبصمة والكود</li><li>حساسات حركة وأبواب</li><li>تنبيهات فورية على الجوال</li></ul><!-- /wp:list --><!-- wp:shortcode -->[ngs_featured_categories slugs="security"]<!-- /wp:shortcode --></div><!-- /wp:group -->',
        ),
        'smart-lighting-category-ar' => array(
            'title'   => 'Smart Lighting Category (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading --><h2>الإضاءة الذكية</h2><!-- /wp:heading --><!-- wp:list --><ul><li>لمبات ذكية متعددة الألوان</li><li>سويتشات ذكية للمفاتيح التقليدية</li><li>شرائط LED وتأثيرات جمالية</li><li>تحكم صوتي وجدولة تلقائية</li></ul><!-- /wp:list --><!-- wp:shortcode -->[ngs_featured_categories slugs="lighting"]<!-- /wp:shortcode --></div><!-- /wp:group -->',
        ),
        'home-automation-category-ar' => array(
            'title'   => 'Home Automation Category (Arabic)',
            'content' => '<!-- wp:group {"className":"ngs-pattern-block"} --><div class="wp-block-group ngs-pattern-block"><!-- wp:heading --><h2>أتمتة المنزل</h2><!-- /wp:heading --><!-- wp:list --><ul><li>هبات ذكية تدعم Zigbee/Matter</li><li>مقابس ذكية مع قياس الطاقة</li><li>سيناريوهات تشغيل تلقائية</li><li>تحكم موحد من تطبيق واحد</li></ul><!-- /wp:list --><!-- wp:shortcode -->[ngs_featured_categories slugs="automation"]<!-- /wp:shortcode --></div><!-- /wp:group -->',
        ),
    );

    foreach ( $patterns as $slug => $pattern ) {
        register_block_pattern(
            'ngs-smarthome/' . $slug,
            array(
                'title'      => $pattern['title'],
                'categories' => array( 'ngs-drafts' ),
                'content'    => $pattern['content'],
            )
        );
    }
}
add_action( 'init', 'ngs_register_block_patterns' );
