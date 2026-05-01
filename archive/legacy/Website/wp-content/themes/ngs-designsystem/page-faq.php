<?php
/**
 * Template Name: FAQ
 *
 * Template for displaying frequently asked questions.
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

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngs-page ngs-page--faq' ); ?>>
			<div class="ngs-container">
				<header class="ngs-page__header" data-ngs-animate="fade-up">
					<h1 class="ngs-page__title"><?php the_title(); ?></h1>

					<?php if ( has_excerpt() ) : ?>
						<div class="ngs-page__excerpt">
							<?php the_excerpt(); ?>
						</div>
					<?php endif; ?>
				</header>

				<!-- FAQ Categories -->
				<div class="ngs-faq-wrapper" data-ngs-animate="fade-up" data-ngs-animate-delay="100">
					<!-- Shipping -->
					<section class="ngs-faq-section">
						<h2 class="ngs-faq-section__title"><?php esc_html_e( 'Shipping & Delivery', 'ngs-designsystem' ); ?></h2>

						<div class="ngs-accordion">
							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-shipping-1">
									<span class="ngs-accordion__title"><?php esc_html_e( 'What are the shipping costs?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-shipping-1" hidden>
									<p><?php esc_html_e( 'Free shipping on all orders over 500 SAR. For orders under 500 SAR, shipping costs 30 SAR within Saudi Arabia.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>

							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-shipping-2">
									<span class="ngs-accordion__title"><?php esc_html_e( 'How long does delivery take?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-shipping-2" hidden>
									<p><?php esc_html_e( 'Delivery takes 2-5 business days depending on your location in Saudi Arabia. Major cities receive faster delivery (2-3 days).', 'ngs-designsystem' ); ?></p>
								</div>
							</div>

							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-shipping-3">
									<span class="ngs-accordion__title"><?php esc_html_e( 'Can I track my order?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-shipping-3" hidden>
									<p><?php esc_html_e( 'Yes, you will receive a tracking number via email and SMS once your order is shipped. You can track it in your account or using the tracking link.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>
						</div>
					</section>

					<!-- Returns -->
					<section class="ngs-faq-section">
						<h2 class="ngs-faq-section__title"><?php esc_html_e( 'Returns & Refunds', 'ngs-designsystem' ); ?></h2>

						<div class="ngs-accordion">
							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-returns-1">
									<span class="ngs-accordion__title"><?php esc_html_e( 'What is your return policy?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-returns-1" hidden>
									<p><?php esc_html_e( 'We offer 14-day returns on unopened products in original packaging. Products must be in new condition with all accessories and documentation.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>

							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-returns-2">
									<span class="ngs-accordion__title"><?php esc_html_e( 'How do I request a return?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-returns-2" hidden>
									<p><?php esc_html_e( 'Contact our support team via WhatsApp or email with your order number. We will provide you with return instructions and a return shipping label.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>

							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-returns-3">
									<span class="ngs-accordion__title"><?php esc_html_e( 'When will I receive my refund?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-returns-3" hidden>
									<p><?php esc_html_e( 'Refunds are processed within 7-14 business days after we receive and inspect the returned item. The refund will be issued to your original payment method.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>
						</div>
					</section>

					<!-- Products -->
					<section class="ngs-faq-section">
						<h2 class="ngs-faq-section__title"><?php esc_html_e( 'Products & Compatibility', 'ngs-designsystem' ); ?></h2>

						<div class="ngs-accordion">
							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-products-1">
									<span class="ngs-accordion__title"><?php esc_html_e( 'Are products compatible with Saudi Arabia power?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-products-1" hidden>
									<p><?php esc_html_e( 'Yes, all our products are compatible with Saudi Arabia electrical standards (220V/60Hz) and come with appropriate power adapters if needed.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>

							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-products-2">
									<span class="ngs-accordion__title"><?php esc_html_e( 'Do products work with Arabic language?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-products-2" hidden>
									<p><?php esc_html_e( 'Most products support Arabic language through their mobile apps. Voice assistants like Alexa and Google Home support Arabic commands.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>

							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-products-3">
									<span class="ngs-accordion__title"><?php esc_html_e( 'Can I mix different brands and ecosystems?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-products-3" hidden>
									<p><?php esc_html_e( 'Yes, many products work across ecosystems using protocols like Zigbee, Z-Wave, or WiFi. Our team can help you choose compatible products.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>
						</div>
					</section>

					<!-- Technical Support -->
					<section class="ngs-faq-section">
						<h2 class="ngs-faq-section__title"><?php esc_html_e( 'Technical Support', 'ngs-designsystem' ); ?></h2>

						<div class="ngs-accordion">
							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-support-1">
									<span class="ngs-accordion__title"><?php esc_html_e( 'Do you offer installation services?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-support-1" hidden>
									<p><?php esc_html_e( 'Yes, we offer professional installation services in major cities. Contact our team for a quote and to schedule an installation.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>

							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-support-2">
									<span class="ngs-accordion__title"><?php esc_html_e( 'What warranty do products have?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-support-2" hidden>
									<p><?php esc_html_e( 'All products come with a minimum 2-year warranty from the manufacturer. We provide local support and handle warranty claims on your behalf.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>

							<div class="ngs-accordion__item">
								<button class="ngs-accordion__trigger" aria-expanded="false" aria-controls="faq-support-3">
									<span class="ngs-accordion__title"><?php esc_html_e( 'How can I get technical support?', 'ngs-designsystem' ); ?></span>
									<span class="ngs-accordion__icon" aria-hidden="true"></span>
								</button>
								<div class="ngs-accordion__content" id="faq-support-3" hidden>
									<p><?php esc_html_e( 'Contact us via WhatsApp, email, or check our Knowledge Base for setup guides and troubleshooting. Our Arabic-speaking team is available to help.', 'ngs-designsystem' ); ?></p>
								</div>
							</div>
						</div>
					</section>
				</div>

				<!-- CTA Section -->
				<section class="ngs-faq-cta" data-ngs-animate="fade-up">
					<h2 class="ngs-faq-cta__title"><?php esc_html_e( 'Still have questions?', 'ngs-designsystem' ); ?></h2>
					<p class="ngs-faq-cta__text"><?php esc_html_e( 'Our support team is here to help you.', 'ngs-designsystem' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="ngs-btn ngs-btn--primary ngs-btn--large">
						<?php esc_html_e( 'Contact Support', 'ngs-designsystem' ); ?>
					</a>
				</section>
			</div>
		</article>

	<?php endwhile; ?>
</main>

<?php
// Schema.org FAQPage JSON-LD
$faq_schema = array(
	'@context' => 'https://schema.org',
	'@type' => 'FAQPage',
	'mainEntity' => array(
		array(
			'@type' => 'Question',
			'name' => __( 'What are the shipping costs?', 'ngs-designsystem' ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text' => __( 'Free shipping on all orders over 500 SAR. For orders under 500 SAR, shipping costs 30 SAR within Saudi Arabia.', 'ngs-designsystem' ),
			),
		),
		array(
			'@type' => 'Question',
			'name' => __( 'What is your return policy?', 'ngs-designsystem' ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text' => __( 'We offer 14-day returns on unopened products in original packaging. Products must be in new condition with all accessories and documentation.', 'ngs-designsystem' ),
			),
		),
	),
);
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>
</script>

<?php
get_footer();
