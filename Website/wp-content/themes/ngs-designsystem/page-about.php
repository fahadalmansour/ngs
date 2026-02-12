<?php
/**
 * Template Name: About
 *
 * Template for displaying the about page.
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

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngs-page ngs-page--about' ); ?>>
			<!-- Hero Section -->
			<section class="ngs-section ngs-about-hero">
				<div class="ngs-container">
					<div class="ngs-about-hero__content" data-ngs-animate="fade-up">
						<h1 class="ngs-about-hero__title"><?php the_title(); ?></h1>

						<?php if ( has_excerpt() ) : ?>
							<div class="ngs-about-hero__excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>

			<!-- Main Content -->
			<section class="ngs-section">
				<div class="ngs-container">
					<div class="ngs-about__content" data-ngs-animate="fade-up">
						<?php the_content(); ?>
					</div>
				</div>
			</section>

			<!-- Stats Section -->
			<section class="ngs-section ngs-section--gray">
				<div class="ngs-container">
					<div class="ngs-stats" data-ngs-animate="fade-up">
						<div class="ngs-stats__item">
							<div class="ngs-stats__number" data-ngs-counter data-ngs-counter-end="5000">0</div>
							<div class="ngs-stats__label"><?php esc_html_e( 'Happy Customers', 'ngs-designsystem' ); ?></div>
						</div>

						<div class="ngs-stats__item">
							<div class="ngs-stats__number" data-ngs-counter data-ngs-counter-end="500">0</div>
							<div class="ngs-stats__label"><?php esc_html_e( 'Smart Products', 'ngs-designsystem' ); ?></div>
						</div>

						<div class="ngs-stats__item">
							<div class="ngs-stats__number" data-ngs-counter data-ngs-counter-end="10">0</div>
							<div class="ngs-stats__label"><?php esc_html_e( 'Supported Ecosystems', 'ngs-designsystem' ); ?></div>
						</div>

						<div class="ngs-stats__item">
							<div class="ngs-stats__number" data-ngs-counter data-ngs-counter-end="100">0</div>
							<div class="ngs-stats__label"><?php esc_html_e( 'Installation Projects', 'ngs-designsystem' ); ?></div>
						</div>
					</div>
				</div>
			</section>

			<!-- Values Section -->
			<section class="ngs-section">
				<div class="ngs-container">
					<h2 class="ngs-section__title" data-ngs-animate="fade-up"><?php esc_html_e( 'Our Values', 'ngs-designsystem' ); ?></h2>

					<div class="ngs-values-grid" data-ngs-animate="fade-up" data-ngs-animate-delay="100">
						<div class="ngs-value-card">
							<div class="ngs-value-card__icon">
								<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<h3 class="ngs-value-card__title"><?php esc_html_e( 'Quality First', 'ngs-designsystem' ); ?></h3>
							<p class="ngs-value-card__description">
								<?php esc_html_e( 'We only offer certified smart home devices from trusted brands to ensure reliability and safety.', 'ngs-designsystem' ); ?>
							</p>
						</div>

						<div class="ngs-value-card">
							<div class="ngs-value-card__icon">
								<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<h3 class="ngs-value-card__title"><?php esc_html_e( 'Expert Support', 'ngs-designsystem' ); ?></h3>
							<p class="ngs-value-card__description">
								<?php esc_html_e( 'Our team provides professional installation and ongoing technical support in Arabic.', 'ngs-designsystem' ); ?>
							</p>
						</div>

						<div class="ngs-value-card">
							<div class="ngs-value-card__icon">
								<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<h3 class="ngs-value-card__title"><?php esc_html_e( 'Customer First', 'ngs-designsystem' ); ?></h3>
							<p class="ngs-value-card__description">
								<?php esc_html_e( 'Your satisfaction is our priority. We offer easy returns and dedicated customer support.', 'ngs-designsystem' ); ?>
							</p>
						</div>

						<div class="ngs-value-card">
							<div class="ngs-value-card__icon">
								<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<h3 class="ngs-value-card__title"><?php esc_html_e( 'Innovation', 'ngs-designsystem' ); ?></h3>
							<p class="ngs-value-card__description">
								<?php esc_html_e( 'We stay ahead by offering the latest smart home technology and compatible ecosystems.', 'ngs-designsystem' ); ?>
							</p>
						</div>
					</div>
				</div>
			</section>

			<!-- Timeline Section -->
			<section class="ngs-section ngs-section--gray">
				<div class="ngs-container">
					<h2 class="ngs-section__title" data-ngs-animate="fade-up"><?php esc_html_e( 'Our Journey', 'ngs-designsystem' ); ?></h2>

					<div class="ngs-timeline" data-ngs-animate="fade-up" data-ngs-animate-delay="100">
						<div class="ngs-timeline__item">
							<div class="ngs-timeline__year">2020</div>
							<div class="ngs-timeline__content">
								<h3 class="ngs-timeline__title"><?php esc_html_e( 'Company Founded', 'ngs-designsystem' ); ?></h3>
								<p class="ngs-timeline__description">
									<?php esc_html_e( 'Started our journey to bring smart home technology to Saudi Arabia.', 'ngs-designsystem' ); ?>
								</p>
							</div>
						</div>

						<div class="ngs-timeline__item">
							<div class="ngs-timeline__year">2021</div>
							<div class="ngs-timeline__content">
								<h3 class="ngs-timeline__title"><?php esc_html_e( 'First 1000 Customers', 'ngs-designsystem' ); ?></h3>
								<p class="ngs-timeline__description">
									<?php esc_html_e( 'Reached our first milestone of serving 1000 satisfied customers.', 'ngs-designsystem' ); ?>
								</p>
							</div>
						</div>

						<div class="ngs-timeline__item">
							<div class="ngs-timeline__year">2022</div>
							<div class="ngs-timeline__content">
								<h3 class="ngs-timeline__title"><?php esc_html_e( 'Expanded Product Range', 'ngs-designsystem' ); ?></h3>
								<p class="ngs-timeline__description">
									<?php esc_html_e( 'Added support for multiple ecosystems and over 500 smart products.', 'ngs-designsystem' ); ?>
								</p>
							</div>
						</div>

						<div class="ngs-timeline__item">
							<div class="ngs-timeline__year">2023</div>
							<div class="ngs-timeline__content">
								<h3 class="ngs-timeline__title"><?php esc_html_e( 'Professional Services', 'ngs-designsystem' ); ?></h3>
								<p class="ngs-timeline__description">
									<?php esc_html_e( 'Launched installation and consultation services across Saudi Arabia.', 'ngs-designsystem' ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>
			</section>
		</article>

	<?php endwhile; ?>
</main>

<?php
get_footer();
