<?php
/**
 * Product Card Template
 *
 * Displays a single product in the loop.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>

<article <?php wc_product_class( 'ngs-product-card', $product ); ?> role="article" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
	<div class="ngs-product-card__inner">

		<!-- Product Image -->
		<div class="ngs-product-card__image">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View %s', 'ngs-designsystem' ), $product->get_name() ) ); ?>">
				<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); ?>
			</a>

			<!-- Badges Container (top-left) -->
			<div class="ngs-product-card__badges">
				<?php
				// AR Badge
				if ( ngs_product_has_3d_model( $product->get_id() ) ) :
				?>
					<span class="ngs-badge ngs-badge--ar" aria-label="<?php esc_attr_e( 'AR Enabled', 'ngs-designsystem' ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
							<path d="M8 14.5L12 10.5L16 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<span><?php esc_html_e( 'AR', 'ngs-designsystem' ); ?></span>
					</span>
				<?php endif; ?>

				<?php
				// Sale Badge
				if ( $product->is_on_sale() ) :
					$regular_price = (float) $product->get_regular_price();
					$sale_price = (float) $product->get_sale_price();

					if ( $regular_price > 0 && $sale_price > 0 ) {
						$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
						?>
						<span class="ngs-badge ngs-badge--sale" aria-label="<?php echo esc_attr( sprintf( __( '%d%% discount', 'ngs-designsystem' ), $percentage ) ); ?>">
							<?php echo esc_html( sprintf( __( '-%d%%', 'ngs-designsystem' ), $percentage ) ); ?>
						</span>
						<?php
					}
				endif;
				?>
			</div>
		</div>

		<!-- Product Info -->
		<div class="ngs-product-card__content">

			<!-- Protocol Badges -->
			<div class="ngs-product-card__protocols">
				<?php ngs_protocol_badges( $product->get_id() ); ?>
			</div>

			<!-- Product Title -->
			<h3 class="ngs-product-card__title">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="ngs-product-card__link">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h3>

			<!-- Rating -->
			<?php if ( wc_review_ratings_enabled() ) : ?>
				<div class="ngs-product-card__rating">
					<?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
				</div>
			<?php endif; ?>

			<!-- Price (always LTR with bidi isolation) -->
			<div class="ngs-product-card__price">
				<span dir="ltr" class="ngs-bidi-ltr">
					<?php echo $product->get_price_html(); ?>
				</span>
			</div>

			<!-- Stock Badge -->
			<div class="ngs-product-card__stock">
				<?php ngs_stock_badge( $product ); ?>
			</div>

		</div>

		<!-- Product Actions -->
		<div class="ngs-product-card__actions">
			<?php
			/**
			 * Add to cart button
			 */
			if ( $product->is_purchasable() && $product->is_in_stock() ) :
				echo apply_filters(
					'woocommerce_loop_add_to_cart_link',
					sprintf(
						'<a href="%s" data-quantity="%s" class="%s" %s aria-label="%s">%s</a>',
						esc_url( $product->add_to_cart_url() ),
						esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
						esc_attr( 'ngs-btn ngs-btn--primary ngs-btn-add-to-cart product_type_' . $product->get_type() ),
						isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
						esc_attr( sprintf( __( 'Add "%s" to cart', 'ngs-designsystem' ), $product->get_name() ) ),
						esc_html( $product->add_to_cart_text() )
					),
					$product,
					$args ?? array()
				);
			else :
				?>
				<button class="ngs-btn ngs-btn--disabled" disabled aria-label="<?php esc_attr_e( 'Product unavailable', 'ngs-designsystem' ); ?>">
					<?php esc_html_e( 'Out of Stock', 'ngs-designsystem' ); ?>
				</button>
				<?php
			endif;
			?>

			<!-- Quick View Button (optional) -->
			<button class="ngs-product-card__quick-view" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Quick view %s', 'ngs-designsystem' ), $product->get_name() ) ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M1 10s3-7 9-7 9 7 9 7-3 7-9 7-9-7-9-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
				</svg>
			</button>
		</div>

	</div>
</article>
