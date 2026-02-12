<?php
/**
 * Product Info Sidebar Template Part
 *
 * Displays product information, price, add to cart, etc.
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
?>

<div class="ngs-product-info">

	<!-- Category Breadcrumb -->
	<?php
	$categories = wc_get_product_category_list( $product_id, ', ' );
	if ( $categories ) :
		?>
		<div class="ngs-product-info__category">
			<?php echo wp_kses_post( $categories ); ?>
		</div>
	<?php endif; ?>

	<!-- Product Title -->
	<h1 class="ngs-product-info__title"><?php echo esc_html( $product->get_name() ); ?></h1>

	<!-- Rating & Reviews -->
	<?php if ( wc_review_ratings_enabled() ) : ?>
		<div class="ngs-product-info__rating">
			<?php
			$rating_count = $product->get_rating_count();
			$review_count = $product->get_review_count();
			$average      = $product->get_average_rating();

			if ( $rating_count > 0 ) :
				?>
				<div class="ngs-rating">
					<div class="ngs-rating__stars" role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %s out of 5', 'ngs-designsystem' ), number_format( $average, 2 ) ) ); ?>">
						<?php echo wc_get_rating_html( $average, $rating_count ); ?>
					</div>
					<a href="#reviews" class="ngs-rating__count">
						<?php
						printf(
							/* translators: %s: review count */
							esc_html( _n( '%s review', '%s reviews', $review_count, 'ngs-designsystem' ) ),
							'<span>' . esc_html( number_format_i18n( $review_count ) ) . '</span>'
						);
						?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Price (LTR with bidi isolation) -->
	<div class="ngs-product-info__price">
		<span dir="ltr" class="ngs-bidi-ltr">
			<?php echo $product->get_price_html(); ?>
		</span>
	</div>

	<!-- Short Description -->
	<?php if ( $product->get_short_description() ) : ?>
		<div class="ngs-product-info__excerpt">
			<?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
		</div>
	<?php endif; ?>

	<!-- Protocol Badges -->
	<div class="ngs-product-info__protocols">
		<?php ngs_protocol_badges( $product_id ); ?>
	</div>

	<!-- Stock Status -->
	<div class="ngs-product-info__stock">
		<?php ngs_stock_badge( $product ); ?>
	</div>

	<!-- Variations / Add to Cart -->
	<div class="ngs-product-info__cart">
		<?php
		/**
		 * Hook: woocommerce_single_product_summary
		 *
		 * @hooked woocommerce_template_single_add_to_cart - 30
		 */
		do_action( 'woocommerce_single_product_summary' );
		?>
	</div>

	<!-- Product Meta (SKU, Categories, Tags) -->
	<div class="ngs-product-info__meta">
		<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
			<div class="ngs-product-meta__item">
				<span class="ngs-product-meta__label"><?php esc_html_e( 'SKU:', 'ngs-designsystem' ); ?></span>
				<span class="ngs-product-meta__value"><?php echo esc_html( $product->get_sku() ? $product->get_sku() : __( 'N/A', 'ngs-designsystem' ) ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( $categories ) : ?>
			<div class="ngs-product-meta__item">
				<span class="ngs-product-meta__label"><?php esc_html_e( 'Categories:', 'ngs-designsystem' ); ?></span>
				<span class="ngs-product-meta__value"><?php echo wp_kses_post( $categories ); ?></span>
			</div>
		<?php endif; ?>

		<?php
		$tags = wc_get_product_tag_list( $product_id, ', ' );
		if ( $tags ) :
			?>
			<div class="ngs-product-meta__item">
				<span class="ngs-product-meta__label"><?php esc_html_e( 'Tags:', 'ngs-designsystem' ); ?></span>
				<span class="ngs-product-meta__value"><?php echo wp_kses_post( $tags ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<!-- Product Attributes -->
	<?php
	$attributes = $product->get_attributes();
	if ( ! empty( $attributes ) ) :
		?>
		<div class="ngs-product-info__attributes">
			<h3 class="ngs-product-info__attributes-title"><?php esc_html_e( 'Key Features', 'ngs-designsystem' ); ?></h3>
			<ul class="ngs-product-attributes">
				<?php
				foreach ( $attributes as $attribute ) :
					if ( ! $attribute->get_visible() ) {
						continue;
					}
					?>
					<li class="ngs-product-attribute">
						<span class="ngs-product-attribute__label"><?php echo esc_html( wc_attribute_label( $attribute->get_name() ) ); ?>:</span>
						<span class="ngs-product-attribute__value">
							<?php
							if ( $attribute->is_taxonomy() ) {
								$values = wc_get_product_terms( $product_id, $attribute->get_name(), array( 'fields' => 'names' ) );
								echo esc_html( implode( ', ', $values ) );
							} else {
								echo esc_html( implode( ', ', $attribute->get_options() ) );
							}
							?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<!-- Share Buttons -->
	<div class="ngs-product-info__share">
		<span class="ngs-product-share__label"><?php esc_html_e( 'Share:', 'ngs-designsystem' ); ?></span>
		<div class="ngs-product-share__buttons">
			<a href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" class="ngs-share-btn ngs-share-btn--whatsapp" aria-label="<?php esc_attr_e( 'Share on WhatsApp', 'ngs-designsystem' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
				</svg>
			</a>
			<a href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode( get_permalink() ); ?>&text=<?php echo rawurlencode( get_the_title() ); ?>" target="_blank" rel="noopener noreferrer" class="ngs-share-btn ngs-share-btn--twitter" aria-label="<?php esc_attr_e( 'Share on X (Twitter)', 'ngs-designsystem' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
				</svg>
			</a>
			<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" class="ngs-share-btn ngs-share-btn--facebook" aria-label="<?php esc_attr_e( 'Share on Facebook', 'ngs-designsystem' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
				</svg>
			</a>
		</div>
	</div>

</div>
