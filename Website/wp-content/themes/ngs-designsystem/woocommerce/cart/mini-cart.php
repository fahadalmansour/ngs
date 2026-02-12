<?php
/**
 * Mini Cart Template
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( ! WC()->cart->is_empty() ) : ?>

	<div class="ngs-mini-cart" role="dialog" aria-label="<?php esc_attr_e( 'Shopping cart', 'ngs-designsystem' ); ?>">

		<div class="ngs-mini-cart__header">
			<h2 class="ngs-mini-cart__title"><?php esc_html_e( 'Shopping Cart', 'ngs-designsystem' ); ?></h2>
			<button class="ngs-mini-cart__close" aria-label="<?php esc_attr_e( 'Close cart', 'ngs-designsystem' ); ?>" data-mini-cart-close>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>

		<ul class="ngs-mini-cart__items woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ?? '' ); ?>">
			<?php
			do_action( 'woocommerce_before_mini_cart_contents' );

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( array( 60, 60 ) ), $cart_item, $cart_item_key );
					$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<li class="ngs-mini-cart__item woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">

						<div class="ngs-mini-cart__item-image">
							<?php
							if ( ! $product_permalink ) {
								echo $thumbnail;
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
							}
							?>
						</div>

						<div class="ngs-mini-cart__item-details">
							<div class="ngs-mini-cart__item-name">
								<?php
								if ( ! $product_permalink ) {
									echo wp_kses_post( $product_name );
								} else {
									echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product_name ) );
								}
								?>
							</div>

							<div class="ngs-mini-cart__item-meta">
								<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
							</div>

							<div class="ngs-mini-cart__item-info">
								<span class="ngs-mini-cart__item-quantity">
									<?php
									echo sprintf(
										/* translators: %s: Product quantity */
										esc_html__( 'Qty: %s', 'ngs-designsystem' ),
										absint( $cart_item['quantity'] )
									);
									?>
								</span>
								<span class="ngs-mini-cart__item-price" dir="ltr">
									<?php echo $product_price; ?>
								</span>
							</div>
						</div>

						<button class="ngs-mini-cart__item-remove"
							aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'ngs-designsystem' ), $product_name ) ); ?>"
							data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>"
							data-product_id="<?php echo esc_attr( $product_id ); ?>"
							data-product_sku="<?php echo esc_attr( $_product->get_sku() ); ?>">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M15 5L5 15M5 5l10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>

					</li>
					<?php
				}
			}

			do_action( 'woocommerce_mini_cart_contents' );
			?>
		</ul>

		<div class="ngs-mini-cart__footer">

			<div class="ngs-mini-cart__total">
				<span class="ngs-mini-cart__total-label"><?php esc_html_e( 'Subtotal', 'ngs-designsystem' ); ?></span>
				<span class="ngs-mini-cart__total-value" dir="ltr"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
			</div>

			<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

			<div class="ngs-mini-cart__actions">
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="ngs-btn ngs-btn--secondary ngs-btn--full">
					<?php esc_html_e( 'View Cart', 'ngs-designsystem' ); ?>
				</a>
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="ngs-btn ngs-btn--primary ngs-btn--full">
					<?php esc_html_e( 'Checkout', 'ngs-designsystem' ); ?>
				</a>
			</div>

			<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

		</div>

	</div>

<?php else : ?>

	<div class="ngs-mini-cart ngs-mini-cart--empty">
		<div class="ngs-mini-cart__header">
			<h2 class="ngs-mini-cart__title"><?php esc_html_e( 'Shopping Cart', 'ngs-designsystem' ); ?></h2>
			<button class="ngs-mini-cart__close" aria-label="<?php esc_attr_e( 'Close cart', 'ngs-designsystem' ); ?>" data-mini-cart-close>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>

		<div class="ngs-mini-cart__empty">
			<div class="ngs-mini-cart__empty-icon" aria-hidden="true">
				<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="40" cy="40" r="40" fill="var(--ngs-color-gray-100)"/>
					<path d="M24 24h8l5 25h17l5-17H32" stroke="var(--ngs-color-gray-400)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="37" cy="57" r="3" fill="var(--ngs-color-gray-400)"/>
					<circle cx="50" cy="57" r="3" fill="var(--ngs-color-gray-400)"/>
				</svg>
			</div>
			<p class="ngs-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'ngs-designsystem' ); ?></p>
			<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="ngs-btn ngs-btn--primary">
				<?php esc_html_e( 'Browse Products', 'ngs-designsystem' ); ?>
			</a>
		</div>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
