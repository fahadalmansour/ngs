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
