<?php
/**
 * Checkout Form Template
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'ngs-designsystem' ) ) );
	return;
}
?>

<main id="main" class="ngs-site-main ngs-checkout" role="main">

	<?php
	// Breadcrumbs
	ngs_breadcrumbs();
	?>

	<div class="ngs-checkout__container">

		<h1 class="ngs-checkout__title"><?php esc_html_e( 'Checkout', 'ngs-designsystem' ); ?></h1>

		<form name="checkout" method="post" class="ngs-checkout__form checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<div class="ngs-checkout__layout">

					<!-- Billing & Shipping Details -->
					<div class="ngs-checkout__details">

						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

						<div class="ngs-checkout__section">
							<h2 class="ngs-checkout__section-title"><?php esc_html_e( 'Billing Details', 'ngs-designsystem' ); ?></h2>

							<?php do_action( 'woocommerce_checkout_billing' ); ?>

							<div class="woocommerce-billing-fields__field-wrapper">
								<?php
								$fields = $checkout->get_checkout_fields( 'billing' );

								foreach ( $fields as $key => $field ) {
									// Saudi phone number with +966 prefix hint
									if ( 'billing_phone' === $key ) {
										$field['placeholder'] = '+966 5XXXXXXXX';
										$field['description'] = esc_html__( 'Enter Saudi mobile number (e.g., +966501234567)', 'ngs-designsystem' );
									}

									woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
								}
								?>
							</div>
						</div>

						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

						<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
							<div class="ngs-checkout__section">
								<h2 class="ngs-checkout__section-title"><?php esc_html_e( 'Shipping Details', 'ngs-designsystem' ); ?></h2>

								<?php do_action( 'woocommerce_checkout_shipping' ); ?>

								<div class="woocommerce-shipping-fields">
									<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

										<div class="shipping_address">
											<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

											<div class="woocommerce-shipping-fields__field-wrapper">
												<?php
												$fields = $checkout->get_checkout_fields( 'shipping' );

												foreach ( $fields as $key => $field ) {
													woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
												}
												?>
											</div>

											<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>
										</div>

									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $checkout->get_checkout_fields( 'order' ) ) : ?>
							<div class="ngs-checkout__section">
								<h2 class="ngs-checkout__section-title"><?php esc_html_e( 'Additional Information', 'ngs-designsystem' ); ?></h2>

								<div class="woocommerce-additional-fields__field-wrapper">
									<?php
									foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) {
										woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
									}
									?>
								</div>
							</div>
						<?php endif; ?>

					</div><!-- .ngs-checkout__details -->

					<!-- Order Review Sidebar -->
					<div class="ngs-checkout__review">

						<h2 class="ngs-checkout__review-title"><?php esc_html_e( 'Your Order', 'ngs-designsystem' ); ?></h2>

						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

						<div id="order_review" class="ngs-checkout__order-review woocommerce-checkout-review-order">

							<!-- Order Items -->
							<div class="ngs-checkout__items">
								<?php
								do_action( 'woocommerce_review_order_before_cart_contents' );

								foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
									$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

									if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
										?>
										<div class="ngs-checkout__item">
											<div class="ngs-checkout__item-image">
												<?php echo $_product->get_image( array( 60, 60 ) ); ?>
											</div>
											<div class="ngs-checkout__item-info">
												<div class="ngs-checkout__item-name">
													<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '&nbsp;'; ?>
													<strong class="ngs-checkout__item-quantity">
														&times;&nbsp;<?php echo absint( $cart_item['quantity'] ); ?>
													</strong>
												</div>
												<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
											</div>
											<div class="ngs-checkout__item-price" dir="ltr">
												<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
											</div>
										</div>
										<?php
									}
								}

								do_action( 'woocommerce_review_order_after_cart_contents' );
								?>
							</div>

							<!-- Order Totals -->
							<div class="ngs-checkout__totals">
								<div class="ngs-checkout__totals-row">
									<span class="ngs-checkout__totals-label"><?php esc_html_e( 'Subtotal', 'ngs-designsystem' ); ?></span>
									<span class="ngs-checkout__totals-value" dir="ltr"><?php wc_cart_totals_subtotal_html(); ?></span>
								</div>

								<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
									<div class="ngs-checkout__totals-row">
										<span class="ngs-checkout__totals-label"><?php echo esc_html( sprintf( __( 'Coupon: %s', 'ngs-designsystem' ), $code ) ); ?></span>
										<span class="ngs-checkout__totals-value" dir="ltr"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
									</div>
								<?php endforeach; ?>

								<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
									<div class="ngs-checkout__totals-row">
										<span class="ngs-checkout__totals-label"><?php esc_html_e( 'Shipping', 'ngs-designsystem' ); ?></span>
										<span class="ngs-checkout__totals-value" dir="ltr"><?php wc_cart_totals_shipping_html(); ?></span>
									</div>
								<?php endif; ?>

								<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
									<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
										<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
											<div class="ngs-checkout__totals-row">
												<span class="ngs-checkout__totals-label"><?php echo esc_html( $tax->label ); ?></span>
												<span class="ngs-checkout__totals-value" dir="ltr"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
											</div>
										<?php endforeach; ?>
									<?php else : ?>
										<div class="ngs-checkout__totals-row">
											<span class="ngs-checkout__totals-label"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
											<span class="ngs-checkout__totals-value" dir="ltr"><?php wc_cart_totals_taxes_total_html(); ?></span>
										</div>
									<?php endif; ?>
								<?php endif; ?>

								<div class="ngs-checkout__totals-row ngs-checkout__totals-row--total">
									<span class="ngs-checkout__totals-label"><?php esc_html_e( 'Total', 'ngs-designsystem' ); ?></span>
									<span class="ngs-checkout__totals-value ngs-checkout__totals-value--total" dir="ltr"><?php wc_cart_totals_order_total_html(); ?></span>
								</div>
							</div>

							<!-- Payment Methods -->
							<div class="ngs-checkout__payment">
								<?php do_action( 'woocommerce_checkout_before_payment' ); ?>

								<div id="payment" class="woocommerce-checkout-payment">
									<?php if ( WC()->cart->needs_payment() ) : ?>
										<ul class="wc_payment_methods payment_methods methods">
											<?php
											if ( ! empty( $available_gateways = WC()->payment_gateways->get_available_payment_gateways() ) ) {
												foreach ( $available_gateways as $gateway ) {
													wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
												}
											} else {
												echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html( apply_filters( 'woocommerce_no_available_payment_methods_message', __( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance.', 'ngs-designsystem' ) ) ) . '</li>';
											}
											?>
										</ul>
									<?php endif; ?>

									<div class="form-row place-order">
										<noscript>
											<?php esc_html_e( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the Update Totals button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'ngs-designsystem' ); ?>
											<br /><button type="submit" class="ngs-btn ngs-btn--secondary" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'ngs-designsystem' ); ?>"><?php esc_html_e( 'Update totals', 'ngs-designsystem' ); ?></button>
										</noscript>

										<?php wc_get_template( 'checkout/terms.php' ); ?>

										<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

										<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="ngs-btn ngs-btn--primary ngs-btn--full ngs-btn--large" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ?? __( 'Place order', 'ngs-designsystem' ) ) . '" data-value="' . esc_attr( $order_button_text ?? __( 'Place order', 'ngs-designsystem' ) ) . '">' . esc_html( $order_button_text ?? __( 'Place Order', 'ngs-designsystem' ) ) . '</button>' ); ?>

										<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

										<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
									</div>
								</div>

								<?php do_action( 'woocommerce_checkout_after_payment' ); ?>
							</div>

						</div>

						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

					</div><!-- .ngs-checkout__review -->

				</div><!-- .ngs-checkout__layout -->

			<?php endif; ?>

		</form>

	</div>

</main>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
