<?php
/**
 * Template Name: Terms & Conditions
 *
 * Template for displaying terms and conditions page.
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
								<li><a href="#acceptance"><?php esc_html_e( 'Acceptance of Terms', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#use-of-site"><?php esc_html_e( 'Use of Site', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#account"><?php esc_html_e( 'Account Registration', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#orders"><?php esc_html_e( 'Orders & Payments', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#shipping"><?php esc_html_e( 'Shipping & Delivery', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#returns"><?php esc_html_e( 'Returns & Refunds', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#intellectual-property"><?php esc_html_e( 'Intellectual Property', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#limitation"><?php esc_html_e( 'Limitation of Liability', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#governing-law"><?php esc_html_e( 'Governing Law', 'ngs-designsystem' ); ?></a></li>
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
							<section id="acceptance" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Acceptance of Terms', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'By accessing and using this website, you accept and agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our website.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="use-of-site" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Use of Site', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'You may use this website for lawful purposes only. You agree not to:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Violate any applicable laws or regulations', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Infringe on intellectual property rights', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Transmit harmful code or malware', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Interfere with website operation', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Attempt unauthorized access to systems', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="account" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Account Registration', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'To make purchases, you must create an account. You are responsible for:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Providing accurate and complete information', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Maintaining the security of your account credentials', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'All activities that occur under your account', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Notifying us immediately of any unauthorized access', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="orders" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Orders & Payments', 'ngs-designsystem' ); ?></h2>
								<ul>
									<li><?php esc_html_e( 'All orders are subject to acceptance and product availability', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Prices are in Saudi Riyals (SAR) and include VAT', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'We reserve the right to refuse or cancel orders', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Payment must be received before order processing', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Accepted payment methods: Mada, Visa, Mastercard, Tamara, Tabby', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="shipping" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Shipping & Delivery', 'ngs-designsystem' ); ?></h2>
								<ul>
									<li><?php esc_html_e( 'We ship within Saudi Arabia only', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Free shipping on orders over 500 SAR', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Delivery times are estimates and not guaranteed', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Risk of loss transfers upon delivery', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="returns" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Returns & Refunds', 'ngs-designsystem' ); ?></h2>
								<ul>
									<li><?php esc_html_e( '14-day return policy on unopened products', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Products must be in original condition with packaging', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Customer is responsible for return shipping costs', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Refunds processed within 7-14 business days', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Some products may not be eligible for return', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="intellectual-property" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Intellectual Property', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'All content on this website, including text, graphics, logos, images, and software, is our property or our licensors\' property and is protected by copyright and trademark laws.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="limitation" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Limitation of Liability', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'We shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use our website or products.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="governing-law" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Governing Law', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'These Terms and Conditions are governed by and construed in accordance with the laws of the Kingdom of Saudi Arabia. Any disputes shall be subject to the exclusive jurisdiction of Saudi courts.', 'ngs-designsystem' ); ?></p>
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
