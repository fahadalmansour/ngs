<?php
/**
 * 404 Error Page Template
 *
 * Template for displaying 404 not found errors.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="ngs-main" role="main">
	<div class="ngs-container">
		<div class="ngs-404" data-ngs-animate="fade-up">
			<div class="ngs-404__icon" aria-hidden="true">
				<svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
					<path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</div>

			<h1 class="ngs-404__title">404</h1>

			<h2 class="ngs-404__subtitle">
				<?php esc_html_e( 'Page Not Found', 'ngs-designsystem' ); ?>
			</h2>

			<p class="ngs-404__message">
				<?php esc_html_e( 'The page you are looking for does not exist or has been moved.', 'ngs-designsystem' ); ?>
			</p>

			<!-- Search Form -->
			<div class="ngs-404__search" data-ngs-animate="fade-up" data-ngs-animate-delay="100">
				<h3 class="ngs-404__search-title"><?php esc_html_e( 'Try searching for what you need', 'ngs-designsystem' ); ?></h3>
				<?php get_search_form(); ?>
			</div>

			<!-- Action Buttons -->
			<div class="ngs-404__actions" data-ngs-animate="fade-up" data-ngs-animate-delay="200">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ngs-btn ngs-btn--primary ngs-btn--large">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M9 22V12h6v10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<?php esc_html_e( 'Back to Home', 'ngs-designsystem' ); ?>
				</a>

				<?php if ( function_exists( 'wc_get_page_id' ) ) : ?>
					<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="ngs-btn ngs-btn--secondary ngs-btn--large">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M3 6h18M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'Browse Shop', 'ngs-designsystem' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<!-- Popular Products -->
			<?php if ( function_exists( 'wc_get_products' ) ) : ?>
				<section class="ngs-404__products" data-ngs-animate="fade-up" data-ngs-animate-delay="300">
					<h3 class="ngs-404__products-title"><?php esc_html_e( 'Popular Products', 'ngs-designsystem' ); ?></h3>

					<?php
					$args = array(
						'limit' => 4,
						'orderby' => 'popularity',
						'status' => 'publish',
						'visibility' => 'visible',
					);
					$products = wc_get_products( $args );

					if ( $products ) :
					?>
						<div class="ngs-products-grid ngs-products-grid--4col">
							<?php foreach ( $products as $product ) : ?>
								<div class="ngs-product-card">
									<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="ngs-product-card__image-link">
										<?php echo wp_kses_post( $product->get_image( 'medium' ) ); ?>
									</a>

									<div class="ngs-product-card__content">
										<h4 class="ngs-product-card__title">
											<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
												<?php echo esc_html( $product->get_name() ); ?>
											</a>
										</h4>

										<div class="ngs-product-card__price">
											<?php echo wp_kses_post( $product->get_price_html() ); ?>
										</div>

										<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="ngs-btn ngs-btn--secondary ngs-btn--small">
											<?php esc_html_e( 'View Product', 'ngs-designsystem' ); ?>
										</a>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<!-- Help Links -->
			<div class="ngs-404__help" data-ngs-animate="fade-up" data-ngs-animate-delay="400">
				<h3 class="ngs-404__help-title"><?php esc_html_e( 'Need Help?', 'ngs-designsystem' ); ?></h3>
				<nav class="ngs-404__help-links" aria-label="<?php esc_attr_e( 'Help navigation', 'ngs-designsystem' ); ?>">
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="ngs-404__help-link">
						<?php esc_html_e( 'Contact Support', 'ngs-designsystem' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="ngs-404__help-link">
						<?php esc_html_e( 'FAQ', 'ngs-designsystem' ); ?>
					</a>
					<?php
					$whatsapp = get_theme_mod( 'ngs_whatsapp_number' );
					if ( $whatsapp ) :
						$whatsapp_url = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp );
					?>
					<a href="<?php echo esc_url( $whatsapp_url ); ?>" class="ngs-404__help-link" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'WhatsApp Us', 'ngs-designsystem' ); ?>
					</a>
					<?php endif; ?>
				</nav>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
