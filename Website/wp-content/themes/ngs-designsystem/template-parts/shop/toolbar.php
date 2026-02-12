<?php
/**
 * Shop Toolbar Template Part
 *
 * Displays result count and sorting dropdown.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$total    = $wp_query->found_posts;
$per_page = $wp_query->query_vars['posts_per_page'];
$current  = max( 1, $wp_query->get( 'paged', 1 ) );
$first    = ( $per_page * ( $current - 1 ) ) + 1;
$last     = min( $total, $per_page * $current );
?>

<div class="ngs-toolbar">

	<!-- Result Count -->
	<div class="ngs-toolbar__count" role="status" aria-live="polite">
		<?php
		if ( 1 === $total ) {
			esc_html_e( 'Showing the single result', 'ngs-designsystem' );
		} elseif ( $total <= $per_page || -1 === $per_page ) {
			/* translators: %d: total results */
			printf( esc_html__( 'Showing all %d results', 'ngs-designsystem' ), absint( $total ) );
		} else {
			/* translators: 1: first result 2: last result 3: total results */
			printf(
				esc_html__( 'Showing %1$d&ndash;%2$d of %3$d results', 'ngs-designsystem' ),
				absint( $first ),
				absint( $last ),
				absint( $total )
			);
		}
		?>
	</div>

	<!-- Sorting Dropdown -->
	<div class="ngs-toolbar__sort">
		<form class="ngs-ordering" method="get">
			<label for="orderby" class="ngs-sr-only"><?php esc_html_e( 'Sort by', 'ngs-designsystem' ); ?></label>
			<select name="orderby" id="orderby" class="ngs-select" aria-label="<?php esc_attr_e( 'Shop order', 'ngs-designsystem' ); ?>" onchange="this.form.submit()">
				<?php
				$catalog_orderby_options = apply_filters(
					'woocommerce_catalog_orderby',
					array(
						'menu_order' => esc_html__( 'Default sorting', 'ngs-designsystem' ),
						'popularity' => esc_html__( 'Sort by popularity', 'ngs-designsystem' ),
						'rating'     => esc_html__( 'Sort by average rating', 'ngs-designsystem' ),
						'date'       => esc_html__( 'Sort by latest', 'ngs-designsystem' ),
						'price'      => esc_html__( 'Sort by price: low to high', 'ngs-designsystem' ),
						'price-desc' => esc_html__( 'Sort by price: high to low', 'ngs-designsystem' ),
					)
				);

				$default_orderby = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
				$orderby         = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : $default_orderby;

				foreach ( $catalog_orderby_options as $id => $name ) {
					echo '<option value="' . esc_attr( $id ) . '" ' . selected( $orderby, $id, false ) . '>' . esc_html( $name ) . '</option>';
				}
				?>
			</select>

			<input type="hidden" name="paged" value="1" />
			<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
		</form>
	</div>

	<!-- View Toggle -->
	<div class="ngs-toolbar__view" role="group" aria-label="<?php esc_attr_e( 'View mode', 'ngs-designsystem' ); ?>">
		<button
			class="ngs-view-toggle ngs-view-toggle--grid is-active"
			data-view="grid"
			aria-label="<?php esc_attr_e( 'Grid view', 'ngs-designsystem' ); ?>"
			aria-pressed="true"
		>
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<rect x="2" y="2" width="6" height="6" rx="1" stroke="currentColor" stroke-width="2"/>
				<rect x="12" y="2" width="6" height="6" rx="1" stroke="currentColor" stroke-width="2"/>
				<rect x="2" y="12" width="6" height="6" rx="1" stroke="currentColor" stroke-width="2"/>
				<rect x="12" y="12" width="6" height="6" rx="1" stroke="currentColor" stroke-width="2"/>
			</svg>
		</button>
		<button
			class="ngs-view-toggle ngs-view-toggle--list"
			data-view="list"
			aria-label="<?php esc_attr_e( 'List view', 'ngs-designsystem' ); ?>"
			aria-pressed="false"
		>
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<line x1="2" y1="4" x2="18" y2="4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				<line x1="2" y1="10" x2="18" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				<line x1="2" y1="16" x2="18" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>
	</div>

</div>
