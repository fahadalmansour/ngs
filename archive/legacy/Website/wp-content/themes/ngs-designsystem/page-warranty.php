<?php
/**
 * Template Name: Warranty
 *
 * Template for displaying warranty information page.
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
								<li><a href="#overview"><?php esc_html_e( 'Warranty Overview', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#coverage"><?php esc_html_e( 'What is Covered', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#not-covered"><?php esc_html_e( 'What is Not Covered', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#claim-process"><?php esc_html_e( 'How to File a Claim', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#duration"><?php esc_html_e( 'Warranty Duration', 'ngs-designsystem' ); ?></a></li>
								<li><a href="#support"><?php esc_html_e( 'Contact Support', 'ngs-designsystem' ); ?></a></li>
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
								<h2><?php esc_html_e( 'Warranty Overview', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'All products purchased from our store come with manufacturer warranty. We act as a local warranty service provider and handle all warranty claims on your behalf to ensure a smooth experience.', 'ngs-designsystem' ); ?></p>
								<div class="ngs-info-box">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<p><strong><?php esc_html_e( 'Minimum 2-year warranty on all smart home products', 'ngs-designsystem' ); ?></strong></p>
								</div>
							</section>

							<section id="coverage" class="ngs-legal-section">
								<h2><?php esc_html_e( 'What is Covered', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'The manufacturer warranty covers:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Manufacturing defects in materials and workmanship', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Failure due to normal use according to product specifications', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Hardware malfunctions under standard operating conditions', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Electrical or mechanical failures not caused by misuse', 'ngs-designsystem' ); ?></li>
								</ul>
								<p><?php esc_html_e( 'Coverage options:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Repair of defective product', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Replacement with same or equivalent model', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Refund in cases where repair or replacement is not possible', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="not-covered" class="ngs-legal-section">
								<h2><?php esc_html_e( 'What is Not Covered', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'The warranty does not cover:', 'ngs-designsystem' ); ?></p>
								<ul>
									<li><?php esc_html_e( 'Damage from misuse, abuse, or improper installation', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Modifications or alterations to the product', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Cosmetic damage (scratches, dents) that does not affect functionality', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Damage from power surges, lightning, or electrical issues', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Water damage or exposure to extreme conditions', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Normal wear and tear', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Consumable items (batteries, bulbs)', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Products with removed or altered serial numbers', 'ngs-designsystem' ); ?></li>
									<li><?php esc_html_e( 'Software issues or compatibility problems', 'ngs-designsystem' ); ?></li>
								</ul>
							</section>

							<section id="claim-process" class="ngs-legal-section">
								<h2><?php esc_html_e( 'How to File a Warranty Claim', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'To file a warranty claim, follow these steps:', 'ngs-designsystem' ); ?></p>
								<ol class="ngs-steps-list">
									<li>
										<strong><?php esc_html_e( 'Contact Support', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'Reach out to our customer support team via WhatsApp, email, or contact form with your order number and product details.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Provide Documentation', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'Submit proof of purchase (invoice or order confirmation), photos of the defect, and serial number.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Troubleshooting', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'Our team will guide you through troubleshooting steps to confirm the issue.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Return Authorization', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'If defect is confirmed, we will issue a return authorization and shipping label.', 'ngs-designsystem' ); ?></p>
									</li>
									<li>
										<strong><?php esc_html_e( 'Inspection & Resolution', 'ngs-designsystem' ); ?></strong>
										<p><?php esc_html_e( 'We inspect the product and process repair, replacement, or refund within 14 business days.', 'ngs-designsystem' ); ?></p>
									</li>
								</ol>
								<div class="ngs-info-box ngs-info-box--success">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<p><?php esc_html_e( 'Warranty service is free of charge. We cover all shipping costs for warranty returns.', 'ngs-designsystem' ); ?></p>
								</div>
							</section>

							<section id="duration" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Warranty Duration', 'ngs-designsystem' ); ?></h2>
								<p><?php esc_html_e( 'Warranty periods vary by product category:', 'ngs-designsystem' ); ?></p>
								<div class="ngs-warranty-table">
									<table>
										<thead>
											<tr>
												<th><?php esc_html_e( 'Product Category', 'ngs-designsystem' ); ?></th>
												<th><?php esc_html_e( 'Warranty Period', 'ngs-designsystem' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td><?php esc_html_e( 'Smart Hubs & Controllers', 'ngs-designsystem' ); ?></td>
												<td><?php esc_html_e( '2 Years', 'ngs-designsystem' ); ?></td>
											</tr>
											<tr>
												<td><?php esc_html_e( 'Smart Lights & Switches', 'ngs-designsystem' ); ?></td>
												<td><?php esc_html_e( '2 Years', 'ngs-designsystem' ); ?></td>
											</tr>
											<tr>
												<td><?php esc_html_e( 'Smart Sensors', 'ngs-designsystem' ); ?></td>
												<td><?php esc_html_e( '2 Years', 'ngs-designsystem' ); ?></td>
											</tr>
											<tr>
												<td><?php esc_html_e( 'Smart Cameras & Doorbells', 'ngs-designsystem' ); ?></td>
												<td><?php esc_html_e( '2-3 Years', 'ngs-designsystem' ); ?></td>
											</tr>
											<tr>
												<td><?php esc_html_e( 'Smart Locks', 'ngs-designsystem' ); ?></td>
												<td><?php esc_html_e( '2-3 Years', 'ngs-designsystem' ); ?></td>
											</tr>
											<tr>
												<td><?php esc_html_e( 'Smart Speakers', 'ngs-designsystem' ); ?></td>
												<td><?php esc_html_e( '1-2 Years', 'ngs-designsystem' ); ?></td>
											</tr>
										</tbody>
									</table>
								</div>
								<p><?php esc_html_e( 'Specific warranty information is listed on each product page. Warranty starts from the date of delivery.', 'ngs-designsystem' ); ?></p>
							</section>

							<section id="support" class="ngs-legal-section">
								<h2><?php esc_html_e( 'Contact Support', 'ngs-designsystem' ); ?></h2>
								<p>
									<?php
									printf(
										/* translators: %s: contact page URL */
										esc_html__( 'For warranty questions or to file a claim, please %s. Our Arabic-speaking support team is ready to assist you.', 'ngs-designsystem' ),
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
