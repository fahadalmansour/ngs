<?php
/**
 * Archive Template
 *
 * Template for displaying archive pages (category, tag, date, author).
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
		<?php ngs_breadcrumbs(); ?>

		<header class="ngs-archive__header" data-ngs-animate="fade-up">
			<h1 class="ngs-archive__title">
				<?php
				if ( is_category() ) {
					single_cat_title();
				} elseif ( is_tag() ) {
					single_tag_title();
				} elseif ( is_author() ) {
					printf(
						/* translators: %s: author name */
						esc_html__( 'Articles by %s', 'ngs-designsystem' ),
						'<span class="vcard">' . get_the_author() . '</span>'
					);
				} elseif ( is_date() ) {
					if ( is_year() ) {
						printf(
							/* translators: %s: year */
							esc_html__( 'Articles from %s', 'ngs-designsystem' ),
							get_the_date( _x( 'Y', 'yearly archives date format', 'ngs-designsystem' ) )
						);
					} elseif ( is_month() ) {
						printf(
							/* translators: %s: month and year */
							esc_html__( 'Articles from %s', 'ngs-designsystem' ),
							get_the_date( _x( 'F Y', 'monthly archives date format', 'ngs-designsystem' ) )
						);
					} elseif ( is_day() ) {
						printf(
							/* translators: %s: date */
							esc_html__( 'Articles from %s', 'ngs-designsystem' ),
							get_the_date()
						);
					}
				} else {
					esc_html_e( 'Archives', 'ngs-designsystem' );
				}
				?>
			</h1>

			<?php
			$description = get_the_archive_description();
			if ( $description ) :
			?>
				<div class="ngs-archive__description">
					<?php echo wp_kses_post( wpautop( $description ) ); ?>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="ngs-posts-grid" data-ngs-animate="fade-up" data-ngs-animate-delay="100">
				<?php while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngs-post-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="ngs-post-card__thumbnail">
								<?php the_post_thumbnail( 'medium', array( 'class' => 'ngs-post-card__image' ) ); ?>
							</a>
						<?php endif; ?>

						<div class="ngs-post-card__content">
							<div class="ngs-post-card__meta">
								<time class="ngs-post-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
									<?php echo esc_html( get_the_date() ); ?>
								</time>

								<?php if ( has_category() ) : ?>
									<span class="ngs-post-card__categories">
										<?php the_category( ', ' ); ?>
									</span>
								<?php endif; ?>
							</div>

							<h2 class="ngs-post-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>

							<div class="ngs-post-card__excerpt">
								<?php the_excerpt(); ?>
							</div>

							<a href="<?php the_permalink(); ?>" class="ngs-post-card__link">
								<?php esc_html_e( 'Read More', 'ngs-designsystem' ); ?>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<?php get_template_part( 'template-parts/components/pagination' ); ?>

		<?php else : ?>

			<div class="ngs-no-content">
				<h2><?php esc_html_e( 'Nothing Found', 'ngs-designsystem' ); ?></h2>
				<p><?php esc_html_e( 'No posts found in this archive. Please try a different search or browse our categories.', 'ngs-designsystem' ); ?></p>

				<?php get_search_form(); ?>
			</div>

		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
