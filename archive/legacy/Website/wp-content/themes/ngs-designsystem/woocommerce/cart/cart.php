<?php
/**
 * Cart Page Template
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_cart' ); ?>

<main id="main" class="ngs-site-main ngs-cart" role="main">

	<?php
	// Breadcrumbs
	ngs_breadcrumbs();
	?>

	<div class="ngs-cart__container">

		<h1 class="ngs-cart__title"><?php esc_html_e( 'Shopping Cart', 'ngs-designsystem' ); ?></h1>

		<form class="ngs-cart__form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

			<?php do_action( 'woocommerce_before_cart_table' ); ?>

			<div class="ngs-cart__layout">

				<!-- Cart Items Table -->
				<div class="ngs-cart__items">
					<table class="ngs-cart__table" role="table" aria-label="<?php esc_attr_e( 'Shopping cart items', 'ngs-designsystem' ); ?>">
						<thead>
							<tr>
								<th scope="col" class="ngs-cart__th ngs-cart__th--product"><?php esc_html_e( 'Product', 'ngs-designsystem' ); ?></th>
								<th scope="col" class="ngs-cart__th ngs-cart__th--price"><?php esc_html_e( 'Price', 'ngs-designsystem' ); ?></th>
								<th scope="col" class="ngs-cart__th ngs-cart__th--quantity"><?php esc_html_e( 'Quantity', 'ngs-designsystem' ); ?></th>
								<th scope="col" class="ngs-cart__th ngs-cart__th--subtotal"><?php esc_html_e( 'Subtotal', 'ngs-designsystem' ); ?></th>
								<th scope="col" class="ngs-cart__th ngs-cart__th--remove"><span class="ngs-sr-only"><?php esc_html_e( 'Remove item', 'ngs-designsystem' ); ?></span></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
								$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
								$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

								if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
									$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
									?>
									<tr class="ngs-cart__row" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">

										<!-- Product Image & Name -->
										<td class="ngs-cart__td ngs-cart__td--product" data-title="<?php esc_attr_e( 'Product', 'ngs-designsystem' ); ?>">
											<div class="ngs-cart__product">
												<div class="ngs-cart__product-image">
													<?php
													$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'thumbnail' ), $cart_item, $cart_item_key );

													if ( ! $product_permalink ) {
														echo $thumbnail;
													} else {
														printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
													}
													?>
												</div>
												<div class="ngs-cart__product-info">
													<?php
													if ( ! $product_permalink ) {
														echo '<span class="ngs-cart__product-name">' . wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '</span>';
													} else {
														echo '<a href="' . esc_url( $product_permalink ) . '" class="ngs-cart__product-name">' . wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '</a>';
													}

													// Metadata
													echo wc_get_formatted_cart_item_data( $cart_item );

													// Backorder notification
													if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
														echo '<p class="ngs-cart__backorder-note">' . esc_html__( 'Available on backorder', 'ngs-designsystem' ) . '</p>';
													}
													?>
												</div>
											</div>
										</td>

										<!-- Price (LTR) -->
										<td class="ngs-cart__td ngs-cart__td--price" data-title="<?php esc_attr_e( 'Price', 'ngs-designsystem' ); ?>">
											<span dir="ltr" class="ngs-bidi-ltr">
												<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
											</span>
										</td>

										<!-- Quantity Stepper -->
										<td class="ngs-cart__td ngs-cart__td--quantity" data-title="<?php esc_attr_e( 'Quantity', 'ngs-designsystem' ); ?>">
											<?php
											if ( $_product->is_sold_individually() ) {
												$product_quantity = '1';
											} else {
												$product_quantity = woocommerce_quantity_input(
													array(
														'input_name'   => "cart[{$cart_item_key}][qty]",
														'input_value'  => $cart_item['quantity'],
														'max_value'    => $_product->get_max_purchase_quantity(),
														'min_value'    => '0',
														'product_name' => $_product->get_name(),
													),
													$_product,
													false
												);
											}

											echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
											?>
										</td>

										<!-- Subtotal (LTR) -->
										<td class="ngs-cart__td ngs-cart__td--subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'ngs-designsystem' ); ?>">
											<span dir="ltr" class="ngs-bidi-ltr">
												<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
											</span>
										</td>

										<!-- Remove Button -->
										<td class="ngs-cart__td ngs-cart__td--remove">
											<?php
											echo apply_filters(
												'woocommerce_cart_item_remove_link',
												sprintf(
													'<a href="%s" class="ngs-cart__remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 5L5 15M5 5l10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>',
													esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
													esc_attr( sprintf( __( 'Remove %s from cart', 'ngs-designsystem' ), $_product->get_name() ) ),
													esc_attr( $product_id ),
													esc_attr( $_product->get_sku() )
												),
												$cart_item_key
											);
											?>
										</td>

									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>

					<!-- Coupon & Update Cart -->
					<div class="ngs-cart__actions">
						<div class="ngs-cart__coupon">
							<label for="coupon_code" class="ngs-sr-only"><?php esc_html_e( 'Coupon code', 'ngs-designsystem' ); ?></label>
							<input type="text" name="coupon_code" class="ngs-cart__coupon-input" id="coupon_code" placeholder="<?php esc_attr_e( 'Coupon code', 'ngs-designsystem' ); ?>" autocomplete="off" />
							<button type="submit" class="ngs-btn ngs-btn--secondary" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'ngs-designsystem' ); ?>">
								<?php esc_html_e( 'Apply', 'ngs-designsystem' ); ?>
							</button>
						</div>
						<button type="submit" class="ngs-btn ngs-btn--secondary" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'ngs-designsystem' ); ?>">
							<?php esc_html_e( 'Update Cart', 'ngs-designsystem' ); ?>
						</button>
					</div>
				</div>

				<!-- Cart Totals Sidebar -->
				<div class="ngs-cart__totals">
					<h2 class="ngs-cart__totals-title"><?php esc_html_e( 'Cart Totals', 'ngs-designsystem' ); ?></h2>

					<?php do_action( 'woocommerce_before_cart_totals' ); ?>

					<div class="ngs-cart__totals-table">
						<div class="ngs-cart__totals-row">
							<span class="ngs-cart__totals-label"><?php esc_html_e( 'Subtotal', 'ngs-designsystem' ); ?></span>
							<span class="ngs-cart__totals-value" dir="ltr"><?php wc_cart_totals_subtotal_html(); ?></span>
						</div>

						<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
							<div class="ngs-cart__totals-row ngs-cart__totals-row--coupon">
								<span class="ngs-cart__totals-label">
									<?php echo esc_html( sprintf( __( 'Coupon: %s', 'ngs-designsystem' ), $code ) ); ?>
									<a href="<?php echo esc_url( add_query_arg( 'remove_coupon', rawurlencode( $code ), wc_get_cart_url() ) ); ?>" class="ngs-cart__remove-coupon" aria-label="<?php echo esc_attr( sprintf( __( 'Remove coupon %s', 'ngs-designsystem' ), $code ) ); ?>">
										<svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
											<path d="M15 5L5 15M5 5l10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</a>
								</span>
								<span class="ngs-cart__totals-value" dir="ltr"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
							</div>
						<?php endforeach; ?>

						<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
							<div class="ngs-cart__totals-row">
								<span class="ngs-cart__totals-label"><?php esc_html_e( 'Shipping', 'ngs-designsystem' ); ?></span>
								<span class="ngs-cart__totals-value" dir="ltr"><?php wc_cart_totals_shipping_html(); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
							<div class="ngs-cart__totals-row">
								<span class="ngs-cart__totals-label"><?php esc_html_e( 'Tax', 'ngs-designsystem' ); ?></span>
								<span class="ngs-cart__totals-value" dir="ltr"><?php wc_cart_totals_taxes_total_html(); ?></span>
							</div>
						<?php endif; ?>

						<div class="ngs-cart__totals-row ngs-cart__totals-row--total">
							<span class="ngs-cart__totals-label"><?php esc_html_e( 'Total', 'ngs-designsystem' ); ?></span>
							<span class="ngs-cart__totals-value ngs-cart__totals-value--total" dir="ltr"><?php wc_cart_totals_order_total_html(); ?></span>
						</div>
					</div>

					<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>

					<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="ngs-btn ngs-btn--primary ngs-btn--full">
						<?php esc_html_e( 'Proceed to Checkout', 'ngs-designsystem' ); ?>
					</a>

					<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="ngs-cart__continue-shopping">
						<?php esc_html_e( 'Continue Shopping', 'ngs-designsystem' ); ?>
					</a>

					<?php do_action( 'woocommerce_after_cart_totals' ); ?>
				</div>

			</div>

			<?php do_action( 'woocommerce_after_cart_table' ); ?>

		</form>

	</div>

</main>

<?php do_action( 'woocommerce_after_cart' ); ?>
