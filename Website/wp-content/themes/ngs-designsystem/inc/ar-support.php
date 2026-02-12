<?php
/**
 * AR/3D Model Support
 *
 * Augmented Reality and 3D model functionality for products.
 * Ported from ngs-smarthome theme with design system styling.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add 3D Model Meta Box to Product Edit Screen
 *
 * @since 1.0.0
 */
function ngs_add_3d_model_meta_box() {
	add_meta_box(
		'ngs_3d_model_meta_box',
		esc_html__( '3D Model (AR)', 'ngs-designsystem' ),
		'ngs_3d_model_meta_box_callback',
		'product',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'ngs_add_3d_model_meta_box' );

/**
 * 3D Model Meta Box Content
 *
 * @since 1.0.0
 * @param WP_Post $post Post object
 */
function ngs_3d_model_meta_box_callback( $post ) {
	wp_nonce_field( 'ngs_3d_model_nonce', 'ngs_3d_model_nonce_field' );

	$model_url = get_post_meta( $post->ID, '_neogen_3d_model_url', true );
	$model_ios_url = get_post_meta( $post->ID, '_neogen_3d_model_ios_url', true );
	?>
	<div class="ngs-3d-meta-box">
		<div class="ngs-ar-info-box">
			<strong><?php esc_html_e( 'AR Feature', 'ngs-designsystem' ); ?></strong>
			<p><?php esc_html_e( 'Add 3D model links to enable "View in Your Room" for customers.', 'ngs-designsystem' ); ?></p>
		</div>

		<div class="ngs-3d-field">
			<label for="ngs_3d_model_url">
				<?php esc_html_e( 'GLB File URL (Android/Desktop)', 'ngs-designsystem' ); ?>
			</label>
			<input
				type="url"
				id="ngs_3d_model_url"
				name="ngs_3d_model_url"
				class="widefat"
				value="<?php echo esc_attr( $model_url ); ?>"
				placeholder="https://example.com/model.glb"
			>
			<p class="description">
				<?php esc_html_e( 'GLB format - for Android and browsers', 'ngs-designsystem' ); ?>
			</p>
		</div>

		<div class="ngs-3d-field">
			<label for="ngs_3d_model_ios_url">
				<?php esc_html_e( 'USDZ File URL (iPhone/iPad)', 'ngs-designsystem' ); ?>
			</label>
			<input
				type="url"
				id="ngs_3d_model_ios_url"
				name="ngs_3d_model_ios_url"
				class="widefat"
				value="<?php echo esc_attr( $model_ios_url ); ?>"
				placeholder="https://example.com/model.usdz"
			>
			<p class="description">
				<?php esc_html_e( 'USDZ format - for Apple devices (optional)', 'ngs-designsystem' ); ?>
			</p>
		</div>

		<div class="ngs-3d-resources">
			<strong><?php esc_html_e( 'Free Model Sources:', 'ngs-designsystem' ); ?></strong>
			<ul>
				<li><a href="https://sketchfab.com/search?type=models&features=downloadable&licenses=7c23a1ba438d4306920229c12afcb5f9" target="_blank" rel="noopener">Sketchfab (CC License)</a></li>
				<li><a href="https://poly.pizza" target="_blank" rel="noopener">Poly.pizza</a></li>
				<li><?php esc_html_e( 'Manufacturer websites', 'ngs-designsystem' ); ?></li>
			</ul>
		</div>
	</div>

	<style>
		.ngs-3d-meta-box .ngs-ar-info-box {
			background: var(--ngs-color-success-50, #ecfdf5);
			border: 1px solid var(--ngs-color-success-600, #059669);
			padding: 12px;
			border-radius: 6px;
			margin-bottom: 16px;
		}
		.ngs-3d-meta-box .ngs-ar-info-box strong {
			color: var(--ngs-color-success-700, #047857);
			display: block;
			margin-bottom: 4px;
		}
		.ngs-3d-meta-box .ngs-ar-info-box p {
			margin: 0;
			font-size: 13px;
			color: var(--ngs-color-success-900, #064e3b);
		}
		.ngs-3d-meta-box .ngs-3d-field {
			margin-bottom: 16px;
		}
		.ngs-3d-meta-box .ngs-3d-field label {
			display: block;
			margin-bottom: 6px;
			font-weight: 600;
			color: var(--ngs-color-gray-900, #111827);
		}
		.ngs-3d-meta-box .ngs-3d-field input {
			width: 100%;
		}
		.ngs-3d-meta-box .ngs-3d-field .description {
			font-size: 12px;
			color: var(--ngs-color-gray-600, #4b5563);
			margin-top: 4px;
		}
		.ngs-3d-meta-box .ngs-3d-resources {
			background: var(--ngs-color-gray-50, #f9fafb);
			padding: 12px;
			border-radius: 6px;
			font-size: 12px;
		}
		.ngs-3d-meta-box .ngs-3d-resources strong {
			display: block;
			margin-bottom: 8px;
			color: var(--ngs-color-gray-900, #111827);
		}
		.ngs-3d-meta-box .ngs-3d-resources ul {
			margin: 0;
			padding-left: 20px;
		}
		.ngs-3d-meta-box .ngs-3d-resources li {
			margin-bottom: 4px;
		}
	</style>
	<?php
}

/**
 * Save 3D Model Meta Box Data
 *
 * @since 1.0.0
 * @param int $post_id Post ID
 */
function ngs_save_3d_model_meta( $post_id ) {
	// Verify nonce
	if ( ! isset( $_POST['ngs_3d_model_nonce_field'] ) ||
		! wp_verify_nonce( $_POST['ngs_3d_model_nonce_field'], 'ngs_3d_model_nonce' ) ) {
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
	if ( isset( $_POST['ngs_3d_model_url'] ) ) {
		$model_url = sanitize_url( $_POST['ngs_3d_model_url'] );
		update_post_meta( $post_id, '_neogen_3d_model_url', $model_url );
	}

	// Save USDZ URL (iOS)
	if ( isset( $_POST['ngs_3d_model_ios_url'] ) ) {
		$model_ios_url = sanitize_url( $_POST['ngs_3d_model_ios_url'] );
		update_post_meta( $post_id, '_neogen_3d_model_ios_url', $model_ios_url );
	}
}
add_action( 'save_post_product', 'ngs_save_3d_model_meta' );

/**
 * Add AR badge to product loop (shop page)
 *
 * @since 1.0.0
 */
function ngs_add_ar_badge_to_loop() {
	global $product;

	if ( ! $product ) {
		return;
	}

	$model_url = get_post_meta( $product->get_id(), '_neogen_3d_model_url', true );

	if ( ! empty( $model_url ) ) {
		echo '<span class="ngs-badge ngs-badge--ar" aria-label="' . esc_attr__( 'AR Enabled', 'ngs-designsystem' ) . '">';
		echo ngs_get_theme_svg( 'ar' );
		echo '<span>' . esc_html__( 'AR', 'ngs-designsystem' ) . '</span>';
		echo '</span>';
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'ngs_add_ar_badge_to_loop', 5 );

/**
 * Allow GLB and USDZ file uploads
 *
 * @since 1.0.0
 * @param array $mimes Allowed MIME types
 * @return array Modified MIME types
 */
function ngs_allow_3d_uploads( $mimes ) {
	$mimes['glb'] = 'model/gltf-binary';
	$mimes['gltf'] = 'model/gltf+json';
	$mimes['usdz'] = 'model/vnd.usdz+zip';
	return $mimes;
}
add_filter( 'upload_mimes', 'ngs_allow_3d_uploads' );

/**
 * Add product data tab for AR info
 *
 * @since 1.0.0
 * @param array $tabs Product tabs
 * @return array Modified tabs
 */
function ngs_add_ar_product_tab( $tabs ) {
	global $product;

	if ( ! $product ) {
		return $tabs;
	}

	$model_url = get_post_meta( $product->get_id(), '_neogen_3d_model_url', true );

	if ( ! empty( $model_url ) ) {
		$tabs['ar_view'] = array(
			'title'    => esc_html__( 'AR View', 'ngs-designsystem' ),
			'priority' => 5,
			'callback' => 'ngs_ar_tab_content',
		);
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'ngs_add_ar_product_tab' );

/**
 * AR Tab Content
 *
 * @since 1.0.0
 */
function ngs_ar_tab_content() {
	global $product;
	?>
	<div class="ngs-ar-tab-content">
		<h2><?php esc_html_e( 'View Product in Augmented Reality', 'ngs-designsystem' ); ?></h2>
		<p><?php esc_html_e( 'Use your smartphone to see this product in your room before buying!', 'ngs-designsystem' ); ?></p>

		<div class="ngs-ar-instructions">
			<h3><?php esc_html_e( 'How to Use', 'ngs-designsystem' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Click the "View 3D" button above', 'ngs-designsystem' ); ?></li>
				<li><?php esc_html_e( 'Tap "View in Your Room"', 'ngs-designsystem' ); ?></li>
				<li><?php esc_html_e( 'Point your camera at the floor or surface', 'ngs-designsystem' ); ?></li>
				<li><?php esc_html_e( 'See the product at actual size in your space!', 'ngs-designsystem' ); ?></li>
			</ol>
		</div>

		<div class="ngs-ar-devices">
			<div class="ngs-ar-device">
				<div class="ngs-ar-device__icon">📱</div>
				<strong><?php esc_html_e( 'iPhone / iPad', 'ngs-designsystem' ); ?></strong>
				<p><?php esc_html_e( 'Works automatically with Safari', 'ngs-designsystem' ); ?></p>
			</div>

			<div class="ngs-ar-device">
				<div class="ngs-ar-device__icon">🤖</div>
				<strong><?php esc_html_e( 'Android', 'ngs-designsystem' ); ?></strong>
				<p><?php esc_html_e( 'Requires Google AR app', 'ngs-designsystem' ); ?></p>
			</div>

			<div class="ngs-ar-device">
				<div class="ngs-ar-device__icon">💻</div>
				<strong><?php esc_html_e( 'Desktop', 'ngs-designsystem' ); ?></strong>
				<p><?php esc_html_e( '3D view only (no AR)', 'ngs-designsystem' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Enqueue model-viewer script on single products with 3D models
 *
 * @since 1.0.0
 */
function ngs_enqueue_model_viewer() {
	if ( ! is_product() ) {
		return;
	}

	global $product;
	if ( ! $product ) {
		return;
	}

	$model_url = get_post_meta( $product->get_id(), '_neogen_3d_model_url', true );

	if ( ! empty( $model_url ) ) {
		wp_enqueue_script(
			'model-viewer',
			'https://ajax.googleapis.com/ajax/libs/model-viewer/3.0.1/model-viewer.min.js',
			array(),
			'3.0.1',
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ngs_enqueue_model_viewer', 20 );
