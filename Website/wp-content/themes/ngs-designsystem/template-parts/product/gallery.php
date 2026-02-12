<?php
/**
 * Product Gallery Template Part
 *
 * Displays product gallery with Image/3D/AR tabs.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product ) {
	return;
}

$product_id = $product->get_id();
$attachment_ids = $product->get_gallery_image_ids();
$has_3d_model = ngs_product_has_3d_model( $product_id );
$model_url = get_post_meta( $product_id, '_neogen_3d_model_url', true );
$model_ios_url = get_post_meta( $product_id, '_neogen_3d_model_ios_url', true );

// Determine available tabs
$tabs = array( 'image' => __( 'Image', 'ngs-designsystem' ) );
if ( $has_3d_model ) {
	$tabs['3d'] = __( '3D View', 'ngs-designsystem' );
	$tabs['ar'] = __( 'AR View', 'ngs-designsystem' );
}
?>

<div class="ngs-gallery">

	<?php if ( count( $tabs ) > 1 ) : ?>
		<!-- Gallery Tabs -->
		<div class="ngs-gallery__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Product view options', 'ngs-designsystem' ); ?>">
			<?php
			$tab_index = 0;
			foreach ( $tabs as $tab_key => $tab_label ) :
				?>
				<button
					class="ngs-gallery__tab <?php echo 0 === $tab_index ? 'is-active' : ''; ?>"
					role="tab"
					id="gallery-tab-<?php echo esc_attr( $tab_key ); ?>"
					aria-controls="gallery-panel-<?php echo esc_attr( $tab_key ); ?>"
					aria-selected="<?php echo 0 === $tab_index ? 'true' : 'false'; ?>"
					data-gallery-tab="<?php echo esc_attr( $tab_key ); ?>"
				>
					<?php echo esc_html( $tab_label ); ?>
				</button>
				<?php
				$tab_index++;
			endforeach;
			?>
		</div>
	<?php endif; ?>

	<!-- Gallery Panels -->
	<div class="ngs-gallery__panels">

		<!-- Image Panel -->
		<div
			class="ngs-gallery__panel ngs-gallery__panel--image is-active"
			id="gallery-panel-image"
			role="tabpanel"
			aria-labelledby="gallery-tab-image"
		>
			<div class="ngs-gallery__main">
				<div class="ngs-gallery__main-image">
					<?php
					if ( has_post_thumbnail() ) {
						echo get_the_post_thumbnail(
							$product_id,
							'woocommerce_single',
							array(
								'loading' => 'eager',
								'alt'     => esc_attr( $product->get_name() ),
							)
						);
					} else {
						echo sprintf( '<img src="%s" alt="%s" class="wp-post-image" loading="eager" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'ngs-designsystem' ) );
					}
					?>
				</div>
			</div>

			<?php if ( ! empty( $attachment_ids ) ) : ?>
				<div class="ngs-gallery__thumbnails">
					<?php
					// Add main image as first thumbnail
					if ( has_post_thumbnail() ) :
						?>
						<button
							class="ngs-gallery__thumbnail is-active"
							data-image-id="<?php echo esc_attr( get_post_thumbnail_id() ); ?>"
							aria-label="<?php esc_attr_e( 'View main product image', 'ngs-designsystem' ); ?>"
						>
							<?php
							echo get_the_post_thumbnail(
								$product_id,
								'woocommerce_gallery_thumbnail',
								array( 'loading' => 'lazy' )
							);
							?>
						</button>
					<?php endif; ?>

					<?php foreach ( $attachment_ids as $attachment_id ) : ?>
						<button
							class="ngs-gallery__thumbnail"
							data-image-id="<?php echo esc_attr( $attachment_id ); ?>"
							aria-label="<?php esc_attr_e( 'View gallery image', 'ngs-designsystem' ); ?>"
						>
							<?php echo wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $has_3d_model ) : ?>
			<!-- 3D View Panel -->
			<div
				class="ngs-gallery__panel ngs-gallery__panel--3d"
				id="gallery-panel-3d"
				role="tabpanel"
				aria-labelledby="gallery-tab-3d"
				hidden
			>
				<div class="ngs-gallery__3d-viewer">
					<model-viewer
						src="<?php echo esc_url( $model_url ); ?>"
						<?php if ( ! empty( $model_ios_url ) ) : ?>
							ios-src="<?php echo esc_url( $model_ios_url ); ?>"
						<?php endif; ?>
						alt="<?php echo esc_attr( sprintf( __( '3D model of %s', 'ngs-designsystem' ), $product->get_name() ) ); ?>"
						camera-controls
						auto-rotate
						shadow-intensity="1"
						loading="lazy"
						ar-modes="webxr scene-viewer quick-look"
						class="ngs-model-viewer"
					>
						<div class="ngs-model-viewer__loading" slot="poster">
							<div class="ngs-spinner" aria-label="<?php esc_attr_e( 'Loading 3D model', 'ngs-designsystem' ); ?>"></div>
						</div>
					</model-viewer>

					<div class="ngs-gallery__3d-controls">
						<p class="ngs-gallery__3d-hint">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" opacity="0.3"/>
								<path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<?php esc_html_e( 'Drag to rotate, scroll to zoom', 'ngs-designsystem' ); ?>
						</p>
					</div>
				</div>
			</div>

			<!-- AR View Panel -->
			<div
				class="ngs-gallery__panel ngs-gallery__panel--ar"
				id="gallery-panel-ar"
				role="tabpanel"
				aria-labelledby="gallery-tab-ar"
				hidden
			>
				<div class="ngs-gallery__ar-info">
					<div class="ngs-gallery__ar-icon" aria-hidden="true">
						<svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
							<path d="M8 14.5L12 10.5L16 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>

					<h3 class="ngs-gallery__ar-title"><?php esc_html_e( 'View in Your Room', 'ngs-designsystem' ); ?></h3>

					<p class="ngs-gallery__ar-description">
						<?php esc_html_e( 'Use your smartphone to see this product in your space before buying.', 'ngs-designsystem' ); ?>
					</p>

					<!-- AR Launch Button -->
					<model-viewer
						src="<?php echo esc_url( $model_url ); ?>"
						<?php if ( ! empty( $model_ios_url ) ) : ?>
							ios-src="<?php echo esc_url( $model_ios_url ); ?>"
						<?php endif; ?>
						ar
						ar-modes="webxr scene-viewer quick-look"
						camera-controls
						class="ngs-model-viewer ngs-model-viewer--ar-only"
						style="width: 1px; height: 1px; position: absolute; opacity: 0;"
					>
						<button slot="ar-button" class="ngs-btn ngs-btn--primary ngs-btn--large">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
								<path d="M9 9h6v6H9V9z" stroke="currentColor" stroke-width="2"/>
							</svg>
							<?php esc_html_e( 'Launch AR', 'ngs-designsystem' ); ?>
						</button>
					</model-viewer>

					<!-- Instructions -->
					<div class="ngs-gallery__ar-instructions">
						<h4 class="ngs-gallery__ar-subtitle"><?php esc_html_e( 'How to Use', 'ngs-designsystem' ); ?></h4>
						<ol class="ngs-gallery__ar-steps">
							<li><?php esc_html_e( 'Tap "Launch AR" button', 'ngs-designsystem' ); ?></li>
							<li><?php esc_html_e( 'Point camera at floor or surface', 'ngs-designsystem' ); ?></li>
							<li><?php esc_html_e( 'Move device to detect the surface', 'ngs-designsystem' ); ?></li>
							<li><?php esc_html_e( 'Tap to place product at actual size', 'ngs-designsystem' ); ?></li>
						</ol>
					</div>

					<!-- Device Compatibility -->
					<div class="ngs-gallery__ar-devices">
						<div class="ngs-gallery__ar-device">
							<span class="ngs-gallery__ar-device-icon" aria-hidden="true">📱</span>
							<strong><?php esc_html_e( 'iPhone/iPad', 'ngs-designsystem' ); ?></strong>
							<small><?php esc_html_e( 'iOS 12+, Safari', 'ngs-designsystem' ); ?></small>
						</div>
						<div class="ngs-gallery__ar-device">
							<span class="ngs-gallery__ar-device-icon" aria-hidden="true">🤖</span>
							<strong><?php esc_html_e( 'Android', 'ngs-designsystem' ); ?></strong>
							<small><?php esc_html_e( 'ARCore compatible', 'ngs-designsystem' ); ?></small>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</div>

</div>
