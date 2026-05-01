<?php
/**
 * Order Confirmation (Thank You) Template
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<main id="main" class="ngs-site-main ngs-thankyou" role="main">

	<div class="ngs-thankyou__container">

		<?php
		if ( $order ) :
			do_action( 'woocommerce_before_thankyou', $order->get_id() );
			?>

			<?php if ( $order->has_status( 'failed' ) ) : ?>

				<!-- Failed Order -->
				<div class="ngs-thankyou__failed">
					<div class="ngs-thankyou__icon ngs-thankyou__icon--error" aria-hidden="true">
						<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="40" cy="40" r="40" fill="var(--ngs-color-error-100)"/>
							<path d="M50 30L30 50M30 30l20 20" stroke="var(--ngs-color-error-600)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>

					<h1 class="ngs-thankyou__title ngs-thankyou__title--error">
						<?php esc_html_e( 'Payment Failed', 'ngs-designsystem' ); ?>
					</h1>

					<p class="ngs-thankyou__message">
						<?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'ngs-designsystem' ); ?>
					</p>

					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="ngs-btn ngs-btn--primary">
						<?php esc_html_e( 'Try Again', 'ngs-designsystem' ); ?>
					</a>
				</div>

			<?php else : ?>

				<!-- Successful Order -->
				<div class="ngs-thankyou__success">

					<div class="ngs-thankyou__icon ngs-thankyou__icon--success" aria-hidden="true">
						<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="40" cy="40" r="40" fill="var(--ngs-color-success-100)"/>
							<path d="M25 40l10 10 20-20" stroke="var(--ngs-color-success-600)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>

					<h1 class="ngs-thankyou__title">
						<?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_heading', __( 'Order Confirmed!', 'ngs-designsystem' ), $order ) ); ?>
					</h1>

					<p class="ngs-thankyou__message">
						<?php esc_html_e( 'Thank you for your purchase! Your order has been received and is being processed.', 'ngs-designsystem' ); ?>
					</p>

					<!-- Order Details -->
					<div class="ngs-thankyou__details">
						<ul class="ngs-thankyou__details-list">
							<li class="ngs-thankyou__detail">
								<span class="ngs-thankyou__detail-label"><?php esc_html_e( 'Order number:', 'ngs-designsystem' ); ?></span>
								<strong class="ngs-thankyou__detail-value"><?php echo esc_html( $order->get_order_number() ); ?></strong>
							</li>

							<li class="ngs-thankyou__detail">
								<span class="ngs-thankyou__detail-label"><?php esc_html_e( 'Date:', 'ngs-designsystem' ); ?></span>
								<strong class="ngs-thankyou__detail-value"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
							</li>

							<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
								<li class="ngs-thankyou__detail">
									<span class="ngs-thankyou__detail-label"><?php esc_html_e( 'Email:', 'ngs-designsystem' ); ?></span>
									<strong class="ngs-thankyou__detail-value"><?php echo esc_html( $order->get_billing_email() ); ?></strong>
								</li>
							<?php endif; ?>

							<li class="ngs-thankyou__detail">
								<span class="ngs-thankyou__detail-label"><?php esc_html_e( 'Total:', 'ngs-designsystem' ); ?></span>
								<strong class="ngs-thankyou__detail-value" dir="ltr"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
							</li>

							<?php if ( $order->get_payment_method_title() ) : ?>
								<li class="ngs-thankyou__detail">
									<span class="ngs-thankyou__detail-label"><?php esc_html_e( 'Payment method:', 'ngs-designsystem' ); ?></span>
									<strong class="ngs-thankyou__detail-value"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
								</li>
							<?php endif; ?>
						</ul>
					</div>

				</div>

				<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>

				<!-- Order Items -->
				<div class="ngs-thankyou__order">
					<h2 class="ngs-thankyou__order-title"><?php esc_html_e( 'Order Details', 'ngs-designsystem' ); ?></h2>

					<div class="ngs-thankyou__items">
						<?php
						foreach ( $order->get_items() as $item_id => $item ) :
							$product = $item->get_product();

							if ( ! $product ) {
								continue;
							}
							?>
							<div class="ngs-thankyou__item">
								<div class="ngs-thankyou__item-image">
									<?php echo $product->get_image( array( 80, 80 ) ); ?>
								</div>
								<div class="ngs-thankyou__item-info">
									<div class="ngs-thankyou__item-name">
										<?php echo wp_kses_post( $item->get_name() ); ?>
									</div>
									<?php if ( $item->get_quantity() > 1 ) : ?>
										<div class="ngs-thankyou__item-quantity">
											<?php echo esc_html( sprintf( __( 'Quantity: %d', 'ngs-designsystem' ), $item->get_quantity() ) ); ?>
										</div>
									<?php endif; ?>
								</div>
								<div class="ngs-thankyou__item-price" dir="ltr">
									<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- Order Totals Summary -->
					<div class="ngs-thankyou__totals">
						<div class="ngs-thankyou__totals-row">
							<span class="ngs-thankyou__totals-label"><?php esc_html_e( 'Subtotal', 'ngs-designsystem' ); ?></span>
							<span class="ngs-thankyou__totals-value" dir="ltr"><?php echo wp_kses_post( $order->get_subtotal_to_display() ); ?></span>
						</div>

						<?php if ( $order->get_shipping_total() > 0 ) : ?>
							<div class="ngs-thankyou__totals-row">
								<span class="ngs-thankyou__totals-label"><?php esc_html_e( 'Shipping', 'ngs-designsystem' ); ?></span>
								<span class="ngs-thankyou__totals-value" dir="ltr"><?php echo wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $order->get_total_tax() > 0 ) : ?>
							<div class="ngs-thankyou__totals-row">
								<span class="ngs-thankyou__totals-label"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
								<span class="ngs-thankyou__totals-value" dir="ltr"><?php echo wp_kses_post( wc_price( $order->get_total_tax() ) ); ?></span>
							</div>
						<?php endif; ?>

						<div class="ngs-thankyou__totals-row ngs-thankyou__totals-row--total">
							<span class="ngs-thankyou__totals-label"><?php esc_html_e( 'Total', 'ngs-designsystem' ); ?></span>
							<span class="ngs-thankyou__totals-value ngs-thankyou__totals-value--total" dir="ltr"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
						</div>
					</div>
				</div>

				<!-- Customer Details -->
				<?php if ( $order->get_billing_address_1() ) : ?>
					<div class="ngs-thankyou__addresses">
						<div class="ngs-thankyou__address">
							<h3 class="ngs-thankyou__address-title"><?php esc_html_e( 'Billing Address', 'ngs-designsystem' ); ?></h3>
							<address class="ngs-thankyou__address-content">
								<?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
							</address>
						</div>

						<?php if ( $order->get_shipping_address_1() ) : ?>
							<div class="ngs-thankyou__address">
								<h3 class="ngs-thankyou__address-title"><?php esc_html_e( 'Shipping Address', 'ngs-designsystem' ); ?></h3>
								<address class="ngs-thankyou__address-content">
									<?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
								</address>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<!-- Actions -->
				<div class="ngs-thankyou__actions">
					<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="ngs-btn ngs-btn--primary ngs-btn--large">
						<?php esc_html_e( 'Continue Shopping', 'ngs-designsystem' ); ?>
					</a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="ngs-btn ngs-btn--secondary ngs-btn--large">
							<?php esc_html_e( 'View Orders', 'ngs-designsystem' ); ?>
						</a>
					<?php endif; ?>
				</div>

			<?php endif; ?>

			<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

		<?php else : ?>

			<!-- No Order Found -->
			<div class="ngs-thankyou__not-found">
				<p class="ngs-thankyou__message">
					<?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'ngs-designsystem' ), null ) ); ?>
				</p>
			</div>

		<?php endif; ?>

	</div>

</main>
