<?php
/**
 * Single Post Template
 *
 * Template for displaying single blog posts.
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

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngs-article' ); ?>>
			<div class="ngs-container">
				<header class="ngs-article__header" data-ngs-animate="fade-up">
					<h1 class="ngs-article__title"><?php the_title(); ?></h1>

					<div class="ngs-article__meta">
						<time class="ngs-article__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( get_the_date() ); ?>
						</time>

						<span class="ngs-article__author" itemprop="author">
							<?php
							printf(
								/* translators: %s: author name */
								esc_html__( 'By %s', 'ngs-designsystem' ),
								'<span class="ngs-article__author-name">' . esc_html( get_the_author() ) . '</span>'
							);
							?>
						</span>

						<?php if ( has_category() ) : ?>
							<span class="ngs-article__categories">
								<?php the_category( ', ' ); ?>
							</span>
						<?php endif; ?>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="ngs-article__featured-image" data-ngs-animate="fade-up" data-ngs-animate-delay="100">
						<?php the_post_thumbnail( 'large', array( 'class' => 'ngs-article__image' ) ); ?>

						<?php
						$caption = get_the_post_thumbnail_caption();
						if ( $caption ) :
						?>
							<figcaption class="ngs-article__image-caption"><?php echo wp_kses_post( $caption ); ?></figcaption>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="ngs-article__content" data-ngs-animate="fade-up" data-ngs-animate-delay="200">
					<?php the_content(); ?>

					<?php
					wp_link_pages( array(
						'before'      => '<nav class="ngs-article__pagination" aria-label="' . esc_attr__( 'Article pages', 'ngs-designsystem' ) . '"><span class="ngs-article__pagination-title">' . esc_html__( 'Pages:', 'ngs-designsystem' ) . '</span>',
						'after'       => '</nav>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					) );
					?>
				</div>

				<?php if ( has_tag() ) : ?>
					<footer class="ngs-article__footer">
						<div class="ngs-article__tags">
							<span class="ngs-article__tags-label"><?php esc_html_e( 'Tags:', 'ngs-designsystem' ); ?></span>
							<?php the_tags( '', ', ', '' ); ?>
						</div>
					</footer>
				<?php endif; ?>

				<nav class="ngs-post-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Post navigation', 'ngs-designsystem' ); ?>">
					<?php
					$prev_post = get_previous_post();
					$next_post = get_next_post();
					?>

					<?php if ( $prev_post ) : ?>
						<div class="ngs-post-navigation__prev">
							<span class="ngs-post-navigation__label"><?php esc_html_e( 'Previous Post', 'ngs-designsystem' ); ?></span>
							<a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>" class="ngs-post-navigation__link">
								<?php echo esc_html( get_the_title( $prev_post ) ); ?>
							</a>
						</div>
					<?php endif; ?>

					<?php if ( $next_post ) : ?>
						<div class="ngs-post-navigation__next">
							<span class="ngs-post-navigation__label"><?php esc_html_e( 'Next Post', 'ngs-designsystem' ); ?></span>
							<a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" class="ngs-post-navigation__link">
								<?php echo esc_html( get_the_title( $next_post ) ); ?>
							</a>
						</div>
					<?php endif; ?>
				</nav>

				<?php
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
				?>
			</div>
		</article>

	<?php endwhile; ?>
</main>

<?php
get_footer();
