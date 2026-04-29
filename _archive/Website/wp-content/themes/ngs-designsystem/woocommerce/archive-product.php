<?php
/**
 * Product Archive Template
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content
 *
 * @hooked woocommerce_output_content_wrapper - 10 (removed)
 */
do_action( 'woocommerce_before_main_content' );
?>

<main id="main" class="ngs-site-main ngs-shop" role="main">

	<?php
	// Breadcrumbs
	ngs_breadcrumbs();
	?>

	<?php if ( have_posts() ) : ?>

		<?php
		/**
		 * Category banner with title and description
		 */
		if ( is_product_category() || is_product_tag() ) :
			$term = get_queried_object();
			$term_id = $term->term_id;
			$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );
			?>
			<div class="ngs-shop__banner">
				<div class="ngs-shop__banner-container">
					<?php if ( $thumbnail_id ) : ?>
						<div class="ngs-shop__banner-image">
							<?php echo wp_get_attachment_image( $thumbnail_id, 'full', false, array( 'loading' => 'eager' ) ); ?>
						</div>
					<?php endif; ?>
					<div class="ngs-shop__banner-content">
						<h1 class="ngs-shop__title"><?php echo esc_html( $term->name ); ?></h1>
						<?php if ( $term->description ) : ?>
							<div class="ngs-shop__description">
								<?php echo wp_kses_post( wpautop( $term->description ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php else : ?>
			<header class="ngs-shop__header">
				<h1 class="ngs-shop__title"><?php woocommerce_page_title(); ?></h1>
				<?php
				/**
				 * Hook: woocommerce_archive_description
				 *
				 * @hooked woocommerce_taxonomy_archive_description - 10
				 * @hooked woocommerce_product_archive_description - 10
				 */
				do_action( 'woocommerce_archive_description' );
				?>
			</header>
		<?php endif; ?>

		<div class="ngs-shop__container">

			<!-- Sidebar with filters -->
			<aside class="ngs-shop__sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Product filters', 'ngs-designsystem' ); ?>">
				<?php get_template_part( 'template-parts/shop/filters' ); ?>
			</aside>

			<!-- Main product area -->
			<div class="ngs-shop__main">

				<?php
				/**
				 * Hook: woocommerce_before_shop_loop
				 *
				 * @hooked woocommerce_output_all_notices - 10
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				?>
				<div class="ngs-shop__toolbar">
					<?php get_template_part( 'template-parts/shop/toolbar' ); ?>
				</div>

				<?php
				/**
				 * Mobile filter toggle button
				 */
				?>
				<button class="ngs-shop__filter-toggle" aria-label="<?php esc_attr_e( 'Open filters', 'ngs-designsystem' ); ?>" aria-expanded="false" data-filter-toggle>
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M2 6h16M5 10h10M8 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span><?php esc_html_e( 'Filters', 'ngs-designsystem' ); ?></span>
				</button>

				<?php
				woocommerce_product_loop_start();

				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();

						/**
						 * Hook: woocommerce_shop_loop
						 */
						do_action( 'woocommerce_shop_loop' );

						wc_get_template_part( 'content', 'product' );
					}
				}

				woocommerce_product_loop_end();

				/**
				 * Hook: woocommerce_after_shop_loop
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				?>
				<div class="ngs-shop__pagination">
					<?php
					the_posts_pagination(
						array(
							'mid_size'           => 2,
							'prev_text'          => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12.5 15l-5-5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' . esc_html__( 'Previous', 'ngs-designsystem' ),
							'next_text'          => esc_html__( 'Next', 'ngs-designsystem' ) . '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7.5 15l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
							'aria_label'         => esc_attr__( 'Product pagination', 'ngs-designsystem' ),
							'screen_reader_text' => esc_html__( 'Products navigation', 'ngs-designsystem' ),
						)
					);
					?>
				</div>

			</div><!-- .ngs-shop__main -->

		</div><!-- .ngs-shop__container -->

	<?php else : ?>

		<?php
		/**
		 * Hook: woocommerce_no_products_found
		 *
		 * @hooked wc_no_products_found - 10
		 */
		?>
		<div class="ngs-shop__empty">
			<div class="ngs-shop__empty-icon" aria-hidden="true">
				<svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="48" cy="48" r="48" fill="var(--ngs-color-gray-100)"/>
					<path d="M32 32h8l6 30h20l6-20H40" stroke="var(--ngs-color-gray-400)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="46" cy="68" r="3" fill="var(--ngs-color-gray-400)"/>
					<circle cx="60" cy="68" r="3" fill="var(--ngs-color-gray-400)"/>
				</svg>
			</div>
			<h2 class="ngs-shop__empty-title"><?php esc_html_e( 'No products found', 'ngs-designsystem' ); ?></h2>
			<p class="ngs-shop__empty-message"><?php esc_html_e( 'Try adjusting your search or filter to find what you\'re looking for.', 'ngs-designsystem' ); ?></p>
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="ngs-btn ngs-btn--primary">
				<?php esc_html_e( 'Browse All Products', 'ngs-designsystem' ); ?>
			</a>
		</div>

	<?php endif; ?>

</main><!-- #main -->

<?php
/**
 * Hook: woocommerce_after_main_content
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (removed)
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
