<?php
/**
 * The Template for displaying single products with AR support
 *
 * @package neogen
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- Model Viewer Library for AR -->
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>

<?php while ( have_posts() ) : the_post(); ?>

    <?php
    global $product;

    // Get product data
    $product_id = $product->get_id();
    $gallery_ids = $product->get_gallery_image_ids();
    $attributes = $product->get_attributes();
    $categories = wc_get_product_category_list( $product_id, ', ' );

    // Get 3D model URL (custom meta field)
    $model_3d_url = get_post_meta( $product_id, '_neogen_3d_model_url', true );
    $model_ios_url = get_post_meta( $product_id, '_neogen_3d_model_ios_url', true ); // .usdz for iOS
    $has_3d_model = ! empty( $model_3d_url );
    ?>

    <!-- Breadcrumbs -->
    <div class="breadcrumb-container" style="background: var(--color-bg-light); padding: 1rem 0; border-bottom: 1px solid var(--color-border);">
        <div class="container">
            <?php woocommerce_breadcrumb(); ?>
        </div>
    </div>

    <div class="container" style="padding: 2rem 1rem 4rem;">

        <div class="product-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 4rem;">

            <!-- Product Gallery with AR Support -->
            <div class="product-gallery">
                <?php
                $main_image = wp_get_attachment_image_src( $product->get_image_id(), 'large' );
                $main_image_full = wp_get_attachment_image_src( $product->get_image_id(), 'full' );
                ?>

                <?php if ( $has_3d_model ) : ?>
                <!-- View Mode Tabs (Image / 3D) -->
                <div class="view-mode-tabs" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <button type="button" class="view-tab active" data-view="image" style="flex: 1; padding: 0.75rem; border: 2px solid var(--color-primary); background: var(--color-primary); color: #fff; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        صورة
                    </button>
                    <button type="button" class="view-tab" data-view="3d" style="flex: 1; padding: 0.75rem; border: 2px solid var(--color-border); background: #fff; color: var(--color-text); border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l9 4.5v9L12 21l-9-4.5v-9L12 3z"></path><path d="M12 12l9-4.5"></path><path d="M12 12v9"></path><path d="M12 12L3 7.5"></path></svg>
                        عرض 3D
                    </button>
                    <button type="button" class="view-tab ar-tab" data-view="ar" style="flex: 1; padding: 0.75rem; border: 2px solid #059669; background: #059669; color: #fff; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                        شاهد بالواقع المعزز
                    </button>
                </div>
                <?php endif; ?>

                <!-- Image View -->
                <div id="image-view" class="main-image" style="background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid var(--color-border); margin-bottom: 1rem; position: relative;">
                    <img id="main-product-image" src="<?php echo esc_url( $main_image[0] ?? wc_placeholder_img_src() ); ?>"
                         alt="<?php echo esc_attr( $product->get_name() ); ?>"
                         style="width: 100%; height: auto; object-fit: contain; padding: 2rem; min-height: 400px;">

                    <?php if ( $has_3d_model ) : ?>
                    <div class="ar-badge" style="position: absolute; top: 1rem; right: 1rem; background: linear-gradient(135deg, #059669, #10b981); color: #fff; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                        يدعم AR
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ( $has_3d_model ) : ?>
                <!-- 3D Model View -->
                <div id="model-view" style="display: none; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid var(--color-border); margin-bottom: 1rem;">
                    <model-viewer
                        id="product-model-viewer"
                        src="<?php echo esc_url( $model_3d_url ); ?>"
                        <?php if ( $model_ios_url ) : ?>ios-src="<?php echo esc_url( $model_ios_url ); ?>"<?php endif; ?>
                        alt="<?php echo esc_attr( $product->get_name() ); ?> - 3D Model"
                        camera-controls
                        auto-rotate
                        ar
                        ar-modes="webxr scene-viewer quick-look"
                        ar-scale="auto"
                        shadow-intensity="1"
                        exposure="1"
                        style="width: 100%; height: 500px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);"
                    >
                        <!-- AR Button Slot -->
                        <button slot="ar-button" class="ar-button-custom" style="
                            position: absolute;
                            bottom: 1rem;
                            left: 50%;
                            transform: translateX(-50%);
                            background: linear-gradient(135deg, #059669, #10b981);
                            color: white;
                            border: none;
                            padding: 1rem 2rem;
                            border-radius: 50px;
                            font-size: 1rem;
                            font-weight: 600;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.4);
                            font-family: inherit;
                        ">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                            شاهد في غرفتك
                        </button>

                        <!-- Loading Indicator -->
                        <div class="model-loading" slot="poster" style="
                            width: 100%;
                            height: 100%;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                        ">
                            <div class="loading-spinner" style="
                                width: 50px;
                                height: 50px;
                                border: 4px solid #e2e8f0;
                                border-top-color: var(--color-primary);
                                border-radius: 50%;
                                animation: spin 1s linear infinite;
                            "></div>
                            <p style="margin-top: 1rem; color: var(--color-text-muted);">جاري تحميل النموذج ثلاثي الأبعاد...</p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress-bar" slot="progress-bar" style="
                            position: absolute;
                            bottom: 0;
                            left: 0;
                            width: 100%;
                            height: 4px;
                            background: #e2e8f0;
                        ">
                            <div class="update-bar" style="background: var(--color-primary); height: 100%; width: 0%;"></div>
                        </div>
                    </model-viewer>

                    <!-- 3D Controls Help -->
                    <div class="model-controls-help" style="padding: 1rem; background: #f8fafc; border-top: 1px solid var(--color-border); display: flex; justify-content: center; gap: 2rem; font-size: 0.85rem; color: var(--color-text-muted);">
                        <span>🖱️ اسحب للتدوير</span>
                        <span>🔍 قرّب/بعّد بالعجلة</span>
                        <span>📱 AR متاح على الجوال</span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $gallery_ids ) || $has_3d_model ) : ?>
                <div class="thumbnail-gallery" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <!-- Main image thumbnail -->
                    <div class="thumb active" data-type="image" style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 2px solid var(--color-primary); cursor: pointer;">
                        <img src="<?php echo esc_url( $main_image[0] ); ?>"
                             data-large="<?php echo esc_url( $main_image[0] ); ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <?php foreach ( $gallery_ids as $gallery_id ) :
                        $gallery_thumb = wp_get_attachment_image_src( $gallery_id, 'thumbnail' );
                        $gallery_large = wp_get_attachment_image_src( $gallery_id, 'large' );
                    ?>
                    <div class="thumb" data-type="image" style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 2px solid var(--color-border); cursor: pointer;">
                        <img src="<?php echo esc_url( $gallery_thumb[0] ); ?>"
                             data-large="<?php echo esc_url( $gallery_large[0] ); ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php endforeach; ?>

                    <?php if ( $has_3d_model ) : ?>
                    <!-- 3D Model Thumbnail -->
                    <div class="thumb thumb-3d" data-type="3d" style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 2px solid var(--color-border); cursor: pointer; background: linear-gradient(135deg, #1F1EFB 0%, #4F4EFC 100%); display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M12 3l9 4.5v9L12 21l-9-4.5v-9L12 3z"></path><path d="M12 12l9-4.5"></path><path d="M12 12v9"></path><path d="M12 12L3 7.5"></path></svg>
                        <span style="color: #fff; font-size: 0.65rem; margin-top: 0.25rem;">3D</span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <!-- Category -->
                <div class="product-category" style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">
                    <?php echo wp_kses_post( $categories ); ?>
                </div>

                <!-- Title -->
                <h1 style="font-size: 2rem; margin-bottom: 1rem;"><?php the_title(); ?></h1>

                <!-- AR Badge for Product -->
                <?php if ( $has_3d_model ) : ?>
                <div style="margin-bottom: 1rem;">
                    <span style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #059669, #10b981); color: #fff; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 500;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M2 12h20"></path></svg>
                        يدعم الواقع المعزز - شاهده في غرفتك!
                    </span>
                </div>
                <?php endif; ?>

                <!-- Rating -->
                <?php if ( $product->get_average_rating() > 0 ) : ?>
                <div class="product-rating" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <?php woocommerce_template_single_rating(); ?>
                </div>
                <?php endif; ?>

                <!-- Price -->
                <div class="product-price" style="margin-bottom: 1.5rem;">
                    <?php woocommerce_template_single_price(); ?>
                </div>

                <!-- Short Description -->
                <div class="product-excerpt" style="color: var(--color-text-muted); line-height: 1.8; margin-bottom: 2rem;">
                    <?php echo wp_kses_post( $product->get_short_description() ); ?>
                </div>

                <!-- Stock Status -->
                <div class="stock-status" style="margin-bottom: 1.5rem;">
                    <?php if ( $product->is_in_stock() ) : ?>
                        <span style="display: inline-flex; align-items: center; gap: 0.5rem; color: #059669; font-weight: 500;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <?php _e( 'متوفر في المخزون', 'neogen-smarthome' ); ?>
                        </span>
                    <?php else : ?>
                        <span style="display: inline-flex; align-items: center; gap: 0.5rem; color: #dc2626; font-weight: 500;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            <?php _e( 'غير متوفر حالياً', 'neogen-smarthome' ); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart -->
                <div class="product-add-to-cart" style="margin-bottom: 2rem;">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>

                <!-- Action Buttons -->
                <div class="product-actions" style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
                    <?php if ( $has_3d_model ) : ?>
                    <!-- AR Quick Button -->
                    <button type="button" id="quick-ar-btn" class="btn" style="flex: 1; min-width: 140px; background: linear-gradient(135deg, #059669, #10b981); border: none; color: #fff; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M2 12h20"></path></svg>
                        شاهد بـ AR
                    </button>
                    <?php endif; ?>

                    <!-- Compare Button (if plugin active) -->
                    <?php if ( class_exists( 'Neogen_Product_Compare' ) ) : ?>
                        <button type="button"
                                class="neogen-compare-btn single"
                                data-product-id="<?php echo esc_attr( $product_id ); ?>"
                                style="flex: 1; min-width: 100px;">
                            <svg class="compare-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 5H2v14h7M15 5h7v14h-7M9 12h6"/>
                            </svg>
                            <span class="compare-text"><?php _e( 'قارن', 'neogen-smarthome' ); ?></span>
                        </button>
                    <?php endif; ?>

                    <!-- WhatsApp Button (if plugin active) -->
                    <?php
                    $whatsapp_number = get_option('neogen_whatsapp_number', '966500000000');
                    $whatsapp_message = 'مرحباً، أريد الاستفسار عن: ' . $product->get_name() . ' - ' . get_permalink();
                    $whatsapp_link = 'https://wa.me/' . $whatsapp_number . '?text=' . urlencode($whatsapp_message);
                    ?>
                    <a href="<?php echo esc_url( $whatsapp_link ); ?>"
                       class="btn btn-outline"
                       style="flex: 1; min-width: 140px; display: flex; align-items: center; justify-content: center; gap: 0.5rem;"
                       target="_blank">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <?php _e( 'استفسر واتساب', 'neogen-smarthome' ); ?>
                    </a>
                </div>

                <!-- Product Meta -->
                <div class="product-meta" style="padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
                    <?php woocommerce_template_single_meta(); ?>
                </div>

                <!-- Trust Badges -->
                <div class="trust-badges" style="display: flex; gap: 1.5rem; margin-top: 2rem; padding: 1.5rem; background: var(--color-bg-light); border-radius: 12px; flex-wrap: wrap;">
                    <div style="text-align: center; flex: 1; min-width: 70px;">
                        <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🛡️</div>
                        <div style="font-size: 0.8rem; color: var(--color-text-muted);"><?php _e( 'ضمان سنة', 'neogen-smarthome' ); ?></div>
                    </div>
                    <div style="text-align: center; flex: 1; min-width: 70px;">
                        <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🚚</div>
                        <div style="font-size: 0.8rem; color: var(--color-text-muted);"><?php _e( 'شحن سريع', 'neogen-smarthome' ); ?></div>
                    </div>
                    <div style="text-align: center; flex: 1; min-width: 70px;">
                        <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">💬</div>
                        <div style="font-size: 0.8rem; color: var(--color-text-muted);"><?php _e( 'دعم فني 24/7', 'neogen-smarthome' ); ?></div>
                    </div>
                    <div style="text-align: center; flex: 1; min-width: 70px;">
                        <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">↩️</div>
                        <div style="font-size: 0.8rem; color: var(--color-text-muted);"><?php _e( 'استرجاع 15 يوم', 'neogen-smarthome' ); ?></div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Product Tabs -->
        <div class="product-tabs" style="margin-bottom: 4rem;">
            <?php woocommerce_output_product_data_tabs(); ?>
        </div>

        <!-- Related Products -->
        <?php woocommerce_output_related_products(); ?>

    </div>

    <style>
    /* Spinner Animation */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Single Product Styles */
    .woocommerce div.product .woocommerce-tabs ul.tabs {
        padding: 0;
        margin: 0 0 2rem;
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--color-border);
    }

    .woocommerce div.product .woocommerce-tabs ul.tabs li {
        background: transparent;
        border: none;
        padding: 0;
        margin: 0;
    }

    .woocommerce div.product .woocommerce-tabs ul.tabs li a {
        padding: 1rem 1.5rem;
        display: block;
        color: var(--color-text-muted);
        font-weight: 600;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
    }

    .woocommerce div.product .woocommerce-tabs ul.tabs li.active a {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
    }

    .woocommerce div.product .woocommerce-tabs .panel {
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        border: 1px solid var(--color-border);
    }

    .woocommerce div.product form.cart {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .woocommerce div.product form.cart .quantity {
        width: auto;
    }

    .woocommerce div.product form.cart .quantity .qty {
        width: 60px;
        padding: 0.75rem;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        text-align: center;
    }

    .woocommerce div.product form.cart .single_add_to_cart_button {
        flex: 1;
        padding: 1rem 2rem !important;
        font-size: 1rem !important;
    }

    .woocommerce div.product .price {
        color: var(--color-primary) !important;
        font-size: 1.75rem !important;
        font-weight: 700 !important;
    }

    .woocommerce div.product .price del {
        color: var(--color-text-muted) !important;
        font-size: 1.25rem !important;
    }

    .woocommerce div.product .price ins {
        text-decoration: none;
    }

    /* Thumbnail Gallery */
    .thumbnail-gallery .thumb:hover,
    .thumbnail-gallery .thumb.active {
        border-color: var(--color-primary) !important;
    }

    .thumbnail-gallery .thumb-3d:hover,
    .thumbnail-gallery .thumb-3d.active {
        border-color: #1F1EFB !important;
        transform: scale(1.05);
    }

    /* View Mode Tabs */
    .view-tab {
        transition: all 0.2s ease;
    }
    .view-tab:hover {
        opacity: 0.9;
    }
    .view-tab.active {
        background: var(--color-primary) !important;
        border-color: var(--color-primary) !important;
        color: #fff !important;
    }
    .ar-tab {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(5, 150, 105, 0); }
    }

    /* Model Viewer Custom Styles */
    model-viewer {
        --poster-color: transparent;
    }
    model-viewer::part(default-ar-button) {
        display: none;
    }

    /* Related Products */
    .woocommerce .related.products h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .product-layout {
            grid-template-columns: 1fr !important;
        }

        .trust-badges {
            justify-content: center;
        }

        .view-mode-tabs {
            flex-direction: column;
        }

        .model-controls-help {
            flex-direction: column;
            gap: 0.5rem !important;
            text-align: center;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // View Mode Switching (Image / 3D / AR)
        var viewTabs = document.querySelectorAll('.view-tab');
        var imageView = document.getElementById('image-view');
        var modelView = document.getElementById('model-view');
        var modelViewer = document.getElementById('product-model-viewer');

        viewTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var view = this.dataset.view;

                // Update tab styles
                viewTabs.forEach(function(t) {
                    t.classList.remove('active');
                    if (!t.classList.contains('ar-tab')) {
                        t.style.background = '#fff';
                        t.style.color = 'var(--color-text)';
                        t.style.borderColor = 'var(--color-border)';
                    }
                });

                if (view === 'image') {
                    this.classList.add('active');
                    this.style.background = 'var(--color-primary)';
                    this.style.color = '#fff';
                    this.style.borderColor = 'var(--color-primary)';
                    if (imageView) imageView.style.display = 'block';
                    if (modelView) modelView.style.display = 'none';
                } else if (view === '3d') {
                    this.classList.add('active');
                    this.style.background = 'var(--color-primary)';
                    this.style.color = '#fff';
                    this.style.borderColor = 'var(--color-primary)';
                    if (imageView) imageView.style.display = 'none';
                    if (modelView) modelView.style.display = 'block';
                } else if (view === 'ar') {
                    // Trigger AR directly
                    if (modelViewer && modelViewer.canActivateAR) {
                        modelViewer.activateAR();
                    } else {
                        // Show 3D view as fallback
                        if (imageView) imageView.style.display = 'none';
                        if (modelView) modelView.style.display = 'block';
                        alert('الواقع المعزز متاح فقط على الأجهزة المحمولة. يمكنك مشاهدة النموذج ثلاثي الأبعاد هنا.');
                    }
                }
            });
        });

        // Quick AR Button
        var quickArBtn = document.getElementById('quick-ar-btn');
        if (quickArBtn && modelViewer) {
            quickArBtn.addEventListener('click', function() {
                if (modelViewer.canActivateAR) {
                    modelViewer.activateAR();
                } else {
                    // Show 3D view
                    if (imageView) imageView.style.display = 'none';
                    if (modelView) modelView.style.display = 'block';
                    // Update tabs
                    viewTabs.forEach(function(t) {
                        if (t.dataset.view === '3d') {
                            t.click();
                        }
                    });
                }
            });
        }

        // Gallery thumbnail click
        document.querySelectorAll('.thumbnail-gallery .thumb').forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                var type = this.dataset.type;

                // Update active state
                document.querySelectorAll('.thumbnail-gallery .thumb').forEach(function(t) {
                    t.classList.remove('active');
                    t.style.borderColor = 'var(--color-border)';
                });
                this.classList.add('active');
                this.style.borderColor = 'var(--color-primary)';

                if (type === '3d') {
                    // Show 3D view
                    if (imageView) imageView.style.display = 'none';
                    if (modelView) modelView.style.display = 'block';
                    // Update tabs
                    viewTabs.forEach(function(t) {
                        t.classList.remove('active');
                        t.style.background = '#fff';
                        t.style.color = 'var(--color-text)';
                        if (t.dataset.view === '3d') {
                            t.classList.add('active');
                            t.style.background = 'var(--color-primary)';
                            t.style.color = '#fff';
                        }
                    });
                } else {
                    // Show image
                    var largeImage = this.querySelector('img').dataset.large;
                    document.getElementById('main-product-image').src = largeImage;
                    if (imageView) imageView.style.display = 'block';
                    if (modelView) modelView.style.display = 'none';
                    // Update tabs
                    viewTabs.forEach(function(t) {
                        t.classList.remove('active');
                        t.style.background = '#fff';
                        t.style.color = 'var(--color-text)';
                        if (t.dataset.view === 'image') {
                            t.classList.add('active');
                            t.style.background = 'var(--color-primary)';
                            t.style.color = '#fff';
                        }
                    });
                }
            });
        });
    });
    </script>

<?php endwhile; ?>

<?php
get_footer();
