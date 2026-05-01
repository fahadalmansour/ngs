<?php
/**
 * Pagination Component
 *
 * Displays pagination for posts or products with accessibility support.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! paginate_links( array( 'echo' => false ) ) ) {
	return;
}
?>

<nav class="ngs-pagination" role="navigation" aria-label="<?php esc_attr_e( 'Pagination', 'ngs-designsystem' ); ?>" data-ngs-animate="fade-up">
	<?php
	$pagination_args = array(
		'mid_size'           => 2,
		'prev_text'          => sprintf(
			'<span class="ngs-pagination__arrow" aria-hidden="true">%s</span><span class="ngs-pagination__label">%s</span>',
			'<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
			esc_html__( 'Previous', 'ngs-designsystem' )
		),
		'next_text'          => sprintf(
			'<span class="ngs-pagination__label">%s</span><span class="ngs-pagination__arrow" aria-hidden="true">%s</span>',
			esc_html__( 'Next', 'ngs-designsystem' ),
			'<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
		),
		'before_page_number' => '<span class="ngs-sr-only">' . esc_html__( 'Page', 'ngs-designsystem' ) . ' </span>',
		'type'               => 'list',
	);

	if ( function_exists( 'woocommerce_pagination' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) {
		woocommerce_pagination();
	} else {
		the_posts_pagination( $pagination_args );
	}
	?>
</nav>
