<?php
/**
 * Template Name: Return Policy
 *
 * Template for displaying return policy page.
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
	<?php while ( have_posts() ) : the_post(); ?>

		<?php ngs_breadcrumbs(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngs-page ngs-page--legal' ); ?>>
			<div class="ngs-container">
				<div class="ngs-legal-layout">
					<!-- Sidebar TOC -->
					<aside class="ngs-legal-sidebar" data-ngs-animate="fade-right">
						<nav class="ngs-legal-toc" aria-label="<?php esc_attr_e( 'Table of Contents', 'ngs-designsystem' ); ?>">
							<h2 class="ngs-legal-toc__title"><?php esc_html_e( 'Table of Contents', 'ngs-designsystem' ); ?></h2>
							<ul class="ngs-legal-toc__list">
								<li><a href="#overview"><?php esc_html_e( 'Return Policy Overview', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#conditions"><?php esc_html_e( 'Return Conditions', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#non-returnable"><?php esc_html_e( 'Non-Returnable Items', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#process"><?php esc_html_e( 'Return Process', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#refunds"><?php esc_html_e( 'Refunds & Timeline', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#exchanges"><?php esc_html_e( 'Exchanges', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#contact"><?php esc_html_e( 'Contact for Returns', 'ngs-designsystem' ); ?></a></li>
							</ul>
						</nav>
					</aside>

					<!-- Main Content -->
					<div class="ngs-legal-content" data-ngs-animate="fade-up">
						<header class="ngs-legal-header">
							<h1 class="ngs-legal__title"><?php the_title(); ?></h1>
							<p class="ngs-legal__updated">
								<?php
								printf(
									/* translators: %s: last modified date */
									esc_html__( 'Last updated: %s', 'ngs-designsystem' ),
									'<time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time>'
								);
								?>
							</p>
						</header>

						<div class="ngs-legal-sections">
							<section id="overview" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Return Policy Overview', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'We want you to be completely satisfied with your purchase. If you are not happy with your order, we offer a 14-day return policy for eligible products.', 'ngs-designsystem' ); ?></p>
								<div class="ngs-info-box">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M9 22V12h6v10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<p><strong><?php esc_html_e( '14-day return window from delivery date', 'ngs-designsystem' ); ?></strong></p>
								</div>
							</section>

							<section id="conditions" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Return Conditions', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'To be eligible for a return, items must meet all of the following conditions:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Returned within 14 days of delivery', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Unopened and unused in original packaging', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'All original accessories, manuals, and components included', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Product seals and stickers intact', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'No physical damage or signs of use', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Serial number matches the original product', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Proof of purchase (invoice or order confirmation)', 'ngs-designsystem' ); ?></li>
								</ul>
								<div class="ngs-info-box ngs-info-box--warning">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<p><?php esc_html_e( 'Opened or used products are not eligible for return except in case of defects covered by warranty.', 'ngs-designsystem' ); ?></p>
								</div>
							</section>

							<section id="non-returnable" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Non-Returnable Items', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'The following items cannot be returned:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Opened or activated products', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Products with broken seals or missing accessories', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Customized or personalized items', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Downloadable software or digital products', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Installation services (non-refundable once performed)', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Sale or clearance items (unless defective)', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="process" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Return Process', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'Follow these steps to return a product:', 'ngs-designsystem' ); ?></p>
								<ol class="ngs-steps-list">
									<li>
										<strong><?php esc_html_e( 'Contact Us', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'Contact our support team within 14 days of delivery via WhatsApp, email, or contact form. Provide your order number and reason for return.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Return Authorization', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'We will review your request and issue a Return Authorization (RA) number if eligible. Do not ship items without an RA number.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Package Securely', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'Pack the product in its original packaging with all accessories. Include the RA number and invoice copy inside the package.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Ship the Return', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'Ship the package using the provided return label or a trackable shipping method. Customer is responsible for return shipping costs.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Inspection', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'We inspect the returned item within 3-5 business days to verify condition and completeness.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Refund Processing', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'If approved, refund is processed to your original payment method within 7-14 business days.', 'ngs-designsystem' ); ?></p>
									</li>
								</ol>
							</section>

							<section id="refunds" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Refunds & Timeline', 'ngs-designsystem' ); ?></h2>
								<h3><?php esc_html_e( 'Refund Method', 'ngs-designsystem' ); ?></h3>
								<p><?php esc_html_e( 'Refunds are issued to the original payment method:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Credit/Debit Card: 7-14 business days', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Tamara/Tabby: 5-10 business days', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Bank Transfer: 3-7 business days', 'ngs-designsystem' ); ?></li>
								</ul>
								<h3><?php esc_html_e( 'Refund Amount', 'ngs-designsystem' ); ?></h3>
								<ul>
									<li><?php esc_html_e( 'Product price will be fully refunded', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Original shipping fees are non-refundable', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Return shipping costs are customer\'s responsibility', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Restocking fees may apply to certain products', 'ngs-designsystem' ); ?></li>
								</ul>
								<div class="ngs-info-box ngs-info-box--success">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<p><?php esc_html_e( 'You will receive an email confirmation once your refund has been processed.', 'ngs-designsystem' ); ?></p>
								</div>
							</section>

							<section id="exchanges" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Exchanges', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'We currently do not offer direct exchanges. To exchange a product:', 'ngs-designsystem' ); ?></p>
								<ol>
									<li><?php esc_html_e( 'Return the original item for a refund (following return process)', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Place a new order for the desired product', 'ngs-designsystem' ); ?></li>
								</ol>
								<p><?php esc_html_e( 'For defective items, please contact us for warranty service or replacement.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="contact" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Contact for Returns', 'ngs-designsystem' ); ?></h2>
								<p>
									<?php
									printf(
										/* translators: %s: contact page URL */
										esc_html__( 'To initiate a return or if you have questions, please %s. Our support team is available in Arabic and English.', 'ngs-designsystem' ),
										'<a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'contact us', 'ngs-designsystem' ) . '</a>'
									);
									?>
								</p>
								<?php
								$whatsapp = get_theme_mod( 'ngs_whatsapp_number' );
								if ( $whatsapp ) :
									$whatsapp_url = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp );
								?>
								<p>
									<?php esc_html_e( 'WhatsApp:', 'ngs-designsystem' ); ?>
									<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html( $whatsapp ); ?>
									</a>
								</p>
								<?php endif; ?>
							</section>

							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</div>
		</article>

	<?php endwhile; ?>
</main>

<?php
get_footer();
