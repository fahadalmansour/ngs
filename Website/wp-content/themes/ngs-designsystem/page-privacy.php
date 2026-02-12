<?php
/**
 * Template Name: Privacy Policy
 *
 * Template for displaying privacy policy page.
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
								<li><a href="#introduction"><?php esc_html_e( 'Introduction', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#information-collection"><?php esc_html_e( 'Information Collection', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#information-use"><?php esc_html_e( 'How We Use Information', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#information-sharing"><?php esc_html_e( 'Information Sharing', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#cookies"><?php esc_html_e( 'Cookies & Tracking', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#data-security"><?php esc_html_e( 'Data Security', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#your-rights"><?php esc_html_e( 'Your Rights', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#contact"><?php esc_html_e( 'Contact Us', 'ngs-designsystem' ); ?></a></li>
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
							<section id="introduction" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Introduction', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services. Please read this privacy policy carefully.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="information-collection" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Information Collection', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'We collect information that you provide directly to us, including:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Name and contact information', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Email address and phone number', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Billing and shipping addresses', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Payment information (processed securely by payment providers)', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Account credentials and preferences', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Communication preferences and history', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="information-use" class="ngs-legal-section">
								<h2><?php esc_html_e( 'How We Use Information', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'We use the information we collect to:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Process and fulfill your orders', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Communicate with you about your orders and account', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Provide customer support and respond to inquiries', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Send promotional communications (with your consent)', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Improve our website and services', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Comply with legal obligations', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="information-sharing" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Information Sharing', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'We may share your information with:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Service providers who help us operate our business (shipping, payment processing)', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Legal authorities when required by law', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Business partners with your explicit consent', 'ngs-designsystem' ); ?></li>
								</ul>
								<p><?php esc_html_e( 'We do not sell your personal information to third parties.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="cookies" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Cookies & Tracking', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'We use cookies and similar tracking technologies to enhance your browsing experience, analyze site traffic, and personalize content. You can control cookies through your browser settings.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="data-security" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Data Security', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'We implement appropriate technical and organizational security measures to protect your personal information. However, no method of transmission over the internet is 100% secure.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="your-rights" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Your Rights', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'You have the right to:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Access your personal information', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Correct inaccurate information', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Request deletion of your information', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Opt-out of marketing communications', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Data portability', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="contact" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Contact Us', 'ngs-designsystem' ); ?></h2>
								<p>
									<?php
									printf(
										/* translators: %s: contact page URL */
										esc_html__( 'If you have questions about this Privacy Policy, please %s.', 'ngs-designsystem' ),
										'<a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'contact us', 'ngs-designsystem' ) . '</a>'
									);
									?>
								</p>
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
