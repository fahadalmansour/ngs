<?php
/**
 * Main Template File
 *
 * Fallback template for displaying posts.
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
		<?php if ( have_posts() ) : ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngs-article' ); ?>>
					<h2 class="ngs-article__title">
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</h2>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="ngs-article__thumbnail">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'medium' ); ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="ngs-article__meta">
						<span class="ngs-article__date">
							<?php echo esc_html( get_the_date() ); ?>
						</span>
						<?php if ( has_category() ) : ?>
							<span class="ngs-article__categories">
								<?php the_category( ', ' ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="ngs-article__content">
						<?php the_excerpt(); ?>
					</div>

					<a href="<?php the_permalink(); ?>" class="ngs-btn ngs-btn--secondary">
						<?php esc_html_e( 'Read More', 'ngs-designsystem' ); ?>
					</a>
				</article>

			<?php endwhile; ?>

			<nav class="ngs-pagination" role="navigation" aria-label="<?php esc_attr_e( 'Posts navigation', 'ngs-designsystem' ); ?>">
				<?php
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => esc_html__( 'Previous', 'ngs-designsystem' ),
					'next_text' => esc_html__( 'Next', 'ngs-designsystem' ),
				) );
				?>
			</nav>

		<?php else : ?>

			<div class="ngs-no-content">
				<h1><?php esc_html_e( 'Nothing Found', 'ngs-designsystem' ); ?></h1>
				<p><?php esc_html_e( 'No content found. Please try a different search or browse our categories.', 'ngs-designsystem' ); ?></p>

				<?php if ( function_exists( 'get_search_form' ) ) : ?>
					<div class="ngs-search-form">
						<?php get_search_form(); ?>
					</div>
				<?php endif; ?>
			</div>

		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
