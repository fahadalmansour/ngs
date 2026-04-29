<?php
/**
 * Template Name: Contact
 *
 * Template for displaying the contact page.
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

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngs-page ngs-page--contact' ); ?>>
			<div class="ngs-container">
				<header class="ngs-page__header" data-ngs-animate="fade-up">
					<h1 class="ngs-page__title"><?php the_title(); ?></h1>

					<?php if ( has_excerpt() ) : ?>
						<div class="ngs-page__excerpt">
							<?php the_excerpt(); ?>
						</div>
					<?php endif; ?>
				</header>

				<div class="ngs-contact-layout">
					<!-- Contact Form -->
					<div class="ngs-contact-form-wrapper" data-ngs-animate="fade-up">
						<h2 class="ngs-contact-form__title"><?php esc_html_e( 'Send us a message', 'ngs-designsystem' ); ?></h2>

						<form class="ngs-form ngs-contact-form" id="ngs-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="ngs_contact_form">
							<?php wp_nonce_field( 'ngs_contact_form', 'ngs_contact_nonce' ); ?>

							<div class="ngs-form__group">
								<label for="contact-name" class="ngs-form__label">
									<?php esc_html_e( 'Name', 'ngs-designsystem' ); ?>
									<span class="ngs-form__required" aria-label="<?php esc_attr_e( 'Required', 'ngs-designsystem' ); ?>">*</span>
								</label>
								<input type="text" id="contact-name" name="contact_name" class="ngs-form__input" required aria-required="true">
							</div>

							<div class="ngs-form__group">
								<label for="contact-email" class="ngs-form__label">
									<?php esc_html_e( 'Email', 'ngs-designsystem' ); ?>
									<span class="ngs-form__required" aria-label="<?php esc_attr_e( 'Required', 'ngs-designsystem' ); ?>">*</span>
								</label>
								<input type="email" id="contact-email" name="contact_email" class="ngs-form__input" required aria-required="true">
							</div>

							<div class="ngs-form__group">
								<label for="contact-phone" class="ngs-form__label">
									<?php esc_html_e( 'Phone Number', 'ngs-designsystem' ); ?>
								</label>
								<input type="tel" id="contact-phone" name="contact_phone" class="ngs-form__input" placeholder="+966">
							</div>

							<div class="ngs-form__group">
								<label for="contact-message" class="ngs-form__label">
									<?php esc_html_e( 'Message', 'ngs-designsystem' ); ?>
									<span class="ngs-form__required" aria-label="<?php esc_attr_e( 'Required', 'ngs-designsystem' ); ?>">*</span>
								</label>
								<textarea id="contact-message" name="contact_message" class="ngs-form__textarea" rows="6" required aria-required="true"></textarea>
							</div>

							<button type="submit" class="ngs-btn ngs-btn--primary ngs-btn--large">
								<?php esc_html_e( 'Send Message', 'ngs-designsystem' ); ?>
							</button>
						</form>
					</div>

					<!-- Contact Info -->
					<div class="ngs-contact-info" data-ngs-animate="fade-up" data-ngs-animate-delay="100">
						<h2 class="ngs-contact-info__title"><?php esc_html_e( 'Contact Information', 'ngs-designsystem' ); ?></h2>

						<div class="ngs-contact-info-cards">
							<!-- WhatsApp -->
							<?php
							$whatsapp = get_theme_mod( 'ngs_whatsapp_number' );
							if ( $whatsapp ) :
								$whatsapp_url = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp );
							?>
							<div class="ngs-contact-card">
								<div class="ngs-contact-card__icon">
									<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</div>
								<h3 class="ngs-contact-card__title"><?php esc_html_e( 'WhatsApp', 'ngs-designsystem' ); ?></h3>
								<a href="<?php echo esc_url( $whatsapp_url ); ?>" class="ngs-contact-card__link" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html( $whatsapp ); ?>
								</a>
								<a href="<?php echo esc_url( $whatsapp_url ); ?>" class="ngs-btn ngs-btn--secondary ngs-btn--small" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Chat on WhatsApp', 'ngs-designsystem' ); ?>
								</a>
							</div>
							<?php endif; ?>

							<!-- Email -->
							<?php
							$support_email = get_theme_mod( 'ngs_support_email', get_option( 'admin_email' ) );
							if ( $support_email ) :
							?>
							<div class="ngs-contact-card">
								<div class="ngs-contact-card__icon">
									<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M22 6l-10 7L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</div>
								<h3 class="ngs-contact-card__title"><?php esc_html_e( 'Email', 'ngs-designsystem' ); ?></h3>
								<a href="mailto:<?php echo esc_attr( $support_email ); ?>" class="ngs-contact-card__link">
									<?php echo esc_html( $support_email ); ?>
								</a>
							</div>
							<?php endif; ?>

							<!-- Address -->
							<div class="ngs-contact-card">
								<div class="ngs-contact-card__icon">
									<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</div>
								<h3 class="ngs-contact-card__title"><?php esc_html_e( 'Address', 'ngs-designsystem' ); ?></h3>
								<p class="ngs-contact-card__text">
									<?php esc_html_e( 'Saudi Arabia', 'ngs-designsystem' ); ?>
								</p>
							</div>
						</div>

						<!-- Map Placeholder -->
						<div class="ngs-contact-map">
							<div class="ngs-contact-map__placeholder">
								<p><?php esc_html_e( 'Google Maps integration available', 'ngs-designsystem' ); ?></p>
								<!-- Add Google Maps embed here -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</article>

	<?php endwhile; ?>
</main>

<?php
get_footer();
