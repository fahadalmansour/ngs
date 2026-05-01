<?php
/**
 * Single Product Template
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
 */
do_action( 'woocommerce_before_main_content' );
?>

<main id="main" class="ngs-site-main ngs-product" role="main">

	<?php
	// Breadcrumbs
	ngs_breadcrumbs();
	?>

	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>

		<?php
		global $product;
		?>

		<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'ngs-product__container', $product ); ?>>

			<div class="ngs-product__layout">

				<!-- Product Gallery (60% width) -->
				<div class="ngs-product__gallery">
					<?php get_template_part( 'template-parts/product/gallery' ); ?>
				</div>

				<!-- Product Info (40% width) -->
				<div class="ngs-product__info">
					<?php get_template_part( 'template-parts/product/info' ); ?>
				</div>

			</div>

			<!-- Trust Badges Row -->
			<div class="ngs-product__trust-section">
				<?php get_template_part( 'template-parts/product/trust-badges' ); ?>
			</div>

			<!-- Product Tabs -->
			<div class="ngs-product__tabs">
				<?php
				/**
				 * Hook: woocommerce_after_single_product_summary
				 *
				 * @hooked woocommerce_output_product_data_tabs - 10
				 * @hooked woocommerce_upsell_display - 15
				 * @hooked woocommerce_output_related_products - 20
				 */
				?>
				<div class="ngs-tabs" role="tablist">
					<?php
					$tabs = apply_filters( 'woocommerce_product_tabs', array() );

					if ( ! empty( $tabs ) ) :
						$tab_index = 0;
						?>
						<div class="ngs-tabs__header">
							<?php foreach ( $tabs as $key => $tab ) : ?>
								<button
									class="ngs-tabs__tab <?php echo 0 === $tab_index ? 'is-active' : ''; ?>"
									id="tab-title-<?php echo esc_attr( $key ); ?>"
									role="tab"
									aria-controls="tab-<?php echo esc_attr( $key ); ?>"
									aria-selected="<?php echo 0 === $tab_index ? 'true' : 'false'; ?>"
									data-tab="<?php echo esc_attr( $key ); ?>"
								>
									<?php echo esc_html( $tab['title'] ); ?>
								</button>
								<?php $tab_index++; ?>
							<?php endforeach; ?>
						</div>

						<div class="ngs-tabs__content">
							<?php
							$tab_index = 0;
							foreach ( $tabs as $key => $tab ) :
								?>
								<div
									class="ngs-tabs__panel <?php echo 0 === $tab_index ? 'is-active' : ''; ?>"
									id="tab-<?php echo esc_attr( $key ); ?>"
									role="tabpanel"
									aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>"
									<?php echo 0 !== $tab_index ? 'hidden' : ''; ?>
								>
									<?php
									if ( isset( $tab['callback'] ) ) {
										call_user_func( $tab['callback'], $key, $tab );
									}
									?>
								</div>
								<?php $tab_index++; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Related Products -->
			<div class="ngs-product__related">
				<?php
				$related_ids = wc_get_related_products( $product->get_id(), 4 );

				if ( ! empty( $related_ids ) ) :
					?>
					<h2 class="ngs-product__related-title"><?php esc_html_e( 'Related Products', 'ngs-designsystem' ); ?></h2>
					<div class="ngs-product__related-grid">
						<?php
						$args = array(
							'post_type'      => 'product',
							'post__in'       => $related_ids,
							'posts_per_page' => 4,
							'orderby'        => 'post__in',
						);

						$related_query = new WP_Query( $args );

						if ( $related_query->have_posts() ) {
							woocommerce_product_loop_start();

							while ( $related_query->have_posts() ) {
								$related_query->the_post();
								wc_get_template_part( 'content', 'product' );
							}

							woocommerce_product_loop_end();
						}

						wp_reset_postdata();
						?>
					</div>
				<?php endif; ?>
			</div>

		</article>

	<?php endwhile; ?>

</main><!-- #main -->

<?php
/**
 * Hook: woocommerce_after_main_content
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
