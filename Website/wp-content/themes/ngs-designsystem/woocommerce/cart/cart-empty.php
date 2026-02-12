<?php
/**
 * Empty Cart Template
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_cart_is_empty' );
?>

<main id="main" class="ngs-site-main ngs-cart-empty" role="main">

	<?php
	// Breadcrumbs
	ngs_breadcrumbs();
	?>

	<div class="ngs-cart-empty__container">

		<div class="ngs-cart-empty__content">

			<!-- Empty Cart Illustration -->
			<div class="ngs-cart-empty__icon" aria-hidden="true">
				<svg width="160" height="160" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="80" cy="80" r="80" fill="var(--ngs-color-gray-100)"/>
					<path d="M50 50h16l10 50h34l10-34H60" stroke="var(--ngs-color-gray-400)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="74" cy="114" r="6" fill="var(--ngs-color-gray-400)"/>
					<circle cx="100" cy="114" r="6" fill="var(--ngs-color-gray-400)"/>
					<line x1="100" y1="70" x2="110" y2="80" stroke="var(--ngs-color-gray-400)" stroke-width="3" stroke-linecap="round"/>
					<line x1="110" y1="70" x2="100" y2="80" stroke="var(--ngs-color-gray-400)" stroke-width="3" stroke-linecap="round"/>
				</svg>
			</div>

			<!-- Empty Message -->
			<h1 class="ngs-cart-empty__title"><?php esc_html_e( 'Your cart is empty', 'ngs-designsystem' ); ?></h1>

			<p class="ngs-cart-empty__message">
				<?php esc_html_e( 'Looks like you haven\'t added any items to your cart yet.', 'ngs-designsystem' ); ?>
			</p>

			<!-- Browse Products CTA -->
			<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="ngs-btn ngs-btn--primary ngs-btn--large">
				<?php esc_html_e( 'Browse Products', 'ngs-designsystem' ); ?>
			</a>

			<?php
			/**
			 * Optional: Show popular products or featured categories
			 */
			do_action( 'woocommerce_empty_cart_after_message' );
			?>

		</div>

	</div>

</main>
