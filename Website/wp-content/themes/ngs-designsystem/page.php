<?php
/**
 * Default Page Template
 *
 * Template for displaying standard pages.
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

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ngs-page' ); ?>>
			<div class="ngs-container">
				<header class="ngs-page__header" data-ngs-animate="fade-up">
					<h1 class="ngs-page__title"><?php the_title(); ?></h1>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="ngs-page__featured-image">
							<?php the_post_thumbnail( 'large', array( 'class' => 'ngs-page__image' ) ); ?>
						</div>
					<?php endif; ?>
				</header>

				<div class="ngs-page__content" data-ngs-animate="fade-up" data-ngs-animate-delay="100">
					<?php the_content(); ?>

					<?php
					wp_link_pages( array(
						'before'      => '<nav class="ngs-page__pagination" aria-label="' . esc_attr__( 'Page navigation', 'ngs-designsystem' ) . '"><span class="ngs-page__pagination-title">' . esc_html__( 'Pages:', 'ngs-designsystem' ) . '</span>',
						'after'       => '</nav>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					) );
					?>
				</div>

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
