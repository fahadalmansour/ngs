<?php
/**
 * Shop Filters Sidebar Template Part
 *
 * Displays product filters (protocol, compatibility, price, stock).
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current filter values
$min_price = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : '';
$max_price = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : '';
$selected_protocols = isset( $_GET['filter_protocol'] ) ? array_map( 'sanitize_text_field', (array) $_GET['filter_protocol'] ) : array();
$selected_compatibility = isset( $_GET['filter_compatibility'] ) ? array_map( 'sanitize_text_field', (array) $_GET['filter_compatibility'] ) : array();
$in_stock_only = isset( $_GET['in_stock_only'] ) ? true : false;
?>

<div class="ngs-filters">

	<div class="ngs-filters__header">
		<h2 class="ngs-filters__title"><?php esc_html_e( 'Filters', 'ngs-designsystem' ); ?></h2>
		<?php if ( ! empty( $selected_protocols ) || ! empty( $selected_compatibility ) || $min_price || $max_price || $in_stock_only ) : ?>
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="ngs-filters__clear">
				<?php esc_html_e( 'Clear All', 'ngs-designsystem' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<form class="ngs-filters__form" method="get" action="">

		<!-- Protocol Filter -->
		<?php
		$protocols = get_terms(
			array(
				'taxonomy'   => 'pa_protocol',
				'hide_empty' => true,
			)
		);

		if ( ! empty( $protocols ) && ! is_wp_error( $protocols ) ) :
			?>
			<div class="ngs-filter-group">
				<h3 class="ngs-filter-group__title"><?php esc_html_e( 'Protocol', 'ngs-designsystem' ); ?></h3>
				<div class="ngs-filter-group__content">
					<?php foreach ( $protocols as $protocol ) : ?>
						<label class="ngs-checkbox">
							<input
								type="checkbox"
								name="filter_protocol[]"
								value="<?php echo esc_attr( $protocol->slug ); ?>"
								<?php checked( in_array( $protocol->slug, $selected_protocols ) ); ?>
								class="ngs-checkbox__input"
							>
							<span class="ngs-checkbox__label">
								<?php echo esc_html( $protocol->name ); ?>
								<span class="ngs-checkbox__count">(<?php echo absint( $protocol->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Compatibility Filter -->
		<?php
		$compatibility_terms = get_terms(
			array(
				'taxonomy'   => 'pa_compatibility',
				'hide_empty' => true,
			)
		);

		if ( ! empty( $compatibility_terms ) && ! is_wp_error( $compatibility_terms ) ) :
			?>
			<div class="ngs-filter-group">
				<h3 class="ngs-filter-group__title"><?php esc_html_e( 'Compatibility', 'ngs-designsystem' ); ?></h3>
				<div class="ngs-filter-group__content">
					<?php foreach ( $compatibility_terms as $term ) : ?>
						<label class="ngs-checkbox">
							<input
								type="checkbox"
								name="filter_compatibility[]"
								value="<?php echo esc_attr( $term->slug ); ?>"
								<?php checked( in_array( $term->slug, $selected_compatibility ) ); ?>
								class="ngs-checkbox__input"
							>
							<span class="ngs-checkbox__label">
								<?php echo esc_html( $term->name ); ?>
								<span class="ngs-checkbox__count">(<?php echo absint( $term->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Price Range Filter -->
		<div class="ngs-filter-group">
			<h3 class="ngs-filter-group__title"><?php esc_html_e( 'Price Range', 'ngs-designsystem' ); ?></h3>
			<div class="ngs-filter-group__content">
				<div class="ngs-price-range">
					<div class="ngs-price-range__inputs">
						<label class="ngs-price-range__label">
							<span class="ngs-sr-only"><?php esc_html_e( 'Minimum price', 'ngs-designsystem' ); ?></span>
							<input
								type="number"
								name="min_price"
								class="ngs-price-range__input"
								placeholder="<?php esc_attr_e( 'Min', 'ngs-designsystem' ); ?>"
								value="<?php echo esc_attr( $min_price ); ?>"
								min="0"
								step="1"
							>
						</label>
						<span class="ngs-price-range__separator">-</span>
						<label class="ngs-price-range__label">
							<span class="ngs-sr-only"><?php esc_html_e( 'Maximum price', 'ngs-designsystem' ); ?></span>
							<input
								type="number"
								name="max_price"
								class="ngs-price-range__input"
								placeholder="<?php esc_attr_e( 'Max', 'ngs-designsystem' ); ?>"
								value="<?php echo esc_attr( $max_price ); ?>"
								min="0"
								step="1"
							>
						</label>
					</div>
					<small class="ngs-price-range__hint"><?php esc_html_e( 'Price in SAR', 'ngs-designsystem' ); ?></small>
				</div>
			</div>
		</div>

		<!-- Stock Status Filter -->
		<div class="ngs-filter-group">
			<h3 class="ngs-filter-group__title"><?php esc_html_e( 'Availability', 'ngs-designsystem' ); ?></h3>
			<div class="ngs-filter-group__content">
				<label class="ngs-checkbox">
					<input
						type="checkbox"
						name="in_stock_only"
						value="1"
						<?php checked( $in_stock_only ); ?>
						class="ngs-checkbox__input"
					>
					<span class="ngs-checkbox__label">
						<?php esc_html_e( 'In Stock Only', 'ngs-designsystem' ); ?>
					</span>
				</label>
			</div>
		</div>

		<!-- AR Enabled Filter -->
		<div class="ngs-filter-group">
			<h3 class="ngs-filter-group__title"><?php esc_html_e( 'Features', 'ngs-designsystem' ); ?></h3>
			<div class="ngs-filter-group__content">
				<label class="ngs-checkbox">
					<input
						type="checkbox"
						name="has_ar"
						value="1"
						<?php checked( isset( $_GET['has_ar'] ) ); ?>
						class="ngs-checkbox__input"
					>
					<span class="ngs-checkbox__label">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="vertical-align: middle; margin-inline-end: 4px;">
							<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2"/>
							<path d="M8 14.5L12 10.5L16 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'AR Enabled', 'ngs-designsystem' ); ?>
					</span>
				</label>
			</div>
		</div>

		<!-- Hidden inputs to preserve other query params -->
		<?php if ( isset( $_GET['orderby'] ) ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $_GET['orderby'] ); ?>">
		<?php endif; ?>

		<!-- Apply Filters Button -->
		<div class="ngs-filters__actions">
			<button type="submit" class="ngs-btn ngs-btn--primary ngs-btn--full">
				<?php esc_html_e( 'Apply Filters', 'ngs-designsystem' ); ?>
			</button>
		</div>

	</form>

	<!-- Mobile Filter Overlay -->
	<div class="ngs-filters-overlay" data-filter-overlay></div>

</div>
